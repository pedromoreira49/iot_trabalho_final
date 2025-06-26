<?php

require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use InfluxDB2\Client;
use InfluxDB2\Model\WritePrecision;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();

// Configurações
$rabbitHost = $_ENV["RABBITMQ_HOST"];
$influxHost = "http://influxdb:8086";
$influxToken = $_ENV["INFLUX_TOKEN"];
$org = $_ENV["INFLUX_ORG"];
$bucket = $_ENV["INFLUX_BUCKET"];

$connection = null;
$maxRetries = 10;
$retryDelay = 5;

// Conexão com RabbitMQ
for ($i = 1; $i < $maxRetries; $i++) {
  try {
    echo "Tentando conectar ao RabbitMQ - Tentativa: {$i}\n";
    $connection = new AMQPStreamConnection(
      $rabbitHost,
      5672,
      $_ENV["RABBITMQ_USER"],
      $_ENV["RABBITMQ_PASSWORD"],
      $_ENV["RABBITMQ_VHOST"]
    );
    echo "Conectado ao RabbitMQ!\n";
    break;
  } catch (Exception $e) {
    echo "Erro na conexão: {$e->getMessage()}\n";
    if ($i == $maxRetries) {
      echo "Número máximo de tentativas excedido. Encerrando.\n";
      exit(1);
    }
  }
  sleep($retryDelay);
}

$channel = $connection->channel();
$channel->exchange_declare('amq.topic', 'topic', false, true, false);
$channel->queue_declare('fila_sensores', false, true, false, false);
$channel->queue_bind('fila_sensores', 'amq.topic', 'sensor.#');
// Conexão com InfluxDB
$client = new Client([
  "url" => $influxHost,
  "token" => $influxToken,
  "timeout" => 5
]);

$writeApi = $client->createWriteApi();

echo "🎧 Aguardando mensagens da fila 'fila_sensores'...\n";

$callback = function ($msg) use ($writeApi, $bucket, $org) {
  echo "📥 Mensagem recebida: {$msg->body}\n";

  $data = json_decode($msg->body, true);
  if (!is_array($data)) {
    echo "⚠️  Mensagem inválida (não é JSON)\n";
    return;
  }

  $timestamp = time(); // precisão em segundos

  // Tratamento por tipo de sensor
  if (isset($data['lux'])) {
    $sensor = $data['sensor'] ?? 'luminosidade_desconhecida';
    $lux = (float)$data['lux'];
    $line = "luminosidade,sensor=$sensor value=$lux";
    $writeApi->write($line, WritePrecision::S, $bucket, $org);
    echo "✅ Enviado [luminosidade]: $line\n";
  } elseif (isset($data['umidade'])) {
    $sensor = $data['sensor'] ?? 'umidade_desconhecida';
    $umidade = (float)$data['umidade'];
    $line = "umidade,sensor=$sensor value=$umidade";
    $writeApi->write($line, WritePrecision::S, $bucket, $org);
    echo "✅ Enviado [umidade]: $line\n";
  } elseif (isset($data['temperatura'])) {
    $sensor = $data['sensor'] ?? 'temperatura_desconhecida';
    $temp = (float)$data['temperatura'];
    $line = "temperatura,sensor=$sensor value=$temp";
    $writeApi->write($line, WritePrecision::S, $bucket, $org);
    echo "✅ Enviado [temperatura]: $line\n";
  } elseif (isset($data['qualidade_ar'])) {
    $sensor = $data['sensor'] ?? 'qualidade_ar_desconhecida';
    $aqi = (float)$data['qualidade_ar'];
    $line = "qualidade_ar,sensor=$sensor value=$aqi";
    $writeApi->write($line, WritePrecision::S, $bucket, $org);
    echo "✅ Enviado [qualidade_ar]: $line\n";
  } else {
    echo "⚠️  Tipo de sensor desconhecido ou ausente\n";
  }
};

$channel->basic_consume('fila_sensores', '', false, true, false, false, $callback);

// Loop infinito
while ($channel->is_consuming()) {
  $channel->wait();
}

$client->close();
