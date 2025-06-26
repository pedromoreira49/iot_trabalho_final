import { connect } from "mqtt";

const client = connect("mqtt://rabbitmq:1883", {
  clientId: "galpao_temperatura",
  username: "",
  password: "",
  reconnectPeriod: 3000, // tenta reconectar a cada 3s
  connectTimeout: 10_000, // timeout de conexão de 10s
});

client.on("connect", () => {
  console.log("Conectado ao broker MQTT");

  setInterval(() => {
    const temp = (Math.random() * 60 + 20).toFixed(2); // 20°C a 80°C

    const payload = {
      temperatura: temp,
      sensor: "galpao_temp",
    };

    const jsonPayload = JSON.stringify(payload);

    client.publish("sensor/galpao/temperatura", jsonPayload);
    console.log(`🌡️ Publicado temperatura: ${temp}°C`);
  }, 10000);
});
