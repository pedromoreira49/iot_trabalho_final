import { connect } from "mqtt";

const client = connect("mqtt://rabbitmq:1883", {
  clientId: "galpao_umidade",
  username: "",
  password: "",
  reconnectPeriod: 3000, // tenta reconectar a cada 3s
  connectTimeout: 10_000, // timeout de conexão de 10s
});

client.on("connect", () => {
  console.log("Conectado ao broker MQTT");

  setInterval(() => {
    const umidade = parseFloat((Math.random() * 40 + 30).toFixed(2)); // 30–70%

    const payload = {
      umidade: umidade,
      sensor: "galpao_umidade", // identifica o sensor no Influx
    };

    const jsonPayload = JSON.stringify(payload);

    client.publish("sensor/galpao/umidade", jsonPayload);
    console.log(`💦 Publicado umidade: ${umidade} AQI`);
  }, 10000);
});
