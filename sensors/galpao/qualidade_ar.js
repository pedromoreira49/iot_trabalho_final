import { connect } from "mqtt";

const client = connect("mqtt://rabbitmq:1883", {
  clientId: "galpao_qualidade_ar",
  username: "",
  password: "",
  reconnectPeriod: 3000, // tenta reconectar a cada 3s
  connectTimeout: 10_000, // timeout de conexão de 10s
});

client.on("connect", () => {
  console.log("Conectado ao broker MQTT");

  setInterval(() => {
    const aqi = parseInt((Math.random() * 57).toFixed(0)); // 0-300

    const payload = {
      qualidade_ar: aqi,
      sensor: "galpao_qualidade_ar",
    };

    const jsonPayload = JSON.stringify(payload);

    client.publish("sensor/galpao/qualidade_ar", jsonPayload);
    console.log(`🫁 Publicado qualidade_ar: ${aqi} AQI`);
  }, 10000);
});
