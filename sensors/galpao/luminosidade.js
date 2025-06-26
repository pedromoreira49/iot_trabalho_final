import { connect } from "mqtt";

const client = connect("mqtt://rabbitmq:1883", {
  clientId: "galpao_luminosidade",
  username: "",
  password: "",
  reconnectPeriod: 3000, // tenta reconectar a cada 3s
  connectTimeout: 10_000, // timeout de conexão de 10s
});

client.on("connect", () => {
  console.log("Conectado ao broker MQTT");

  setInterval(() => {
    const lux = parseFloat((Math.random() * 9900 + 100).toFixed(2)); // 100-10000

    const payload = {
      lux: lux,
      sensor: "galpao_lux",
    };

    const jsonPayload = JSON.stringify(payload);

    client.publish("sensor/galpao/luminosidade", jsonPayload);
    console.log(`🔆 Publicado luminosidade: ${lux} lux`);
  }, 10000);
});
