# Usa Node.js LTS como base
FROM node:20.15.1

# Cria o diretório de trabalho
WORKDIR /usr/src/app

# Copia os arquivos de dependência
COPY package*.json ./

# Instala as dependências
RUN npm install

# Copia o restante do projeto
COPY . .

# Comando padrão
CMD ["npm", "run", "all"]
