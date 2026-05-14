FROM node:24-alpine AS builder

WORKDIR /app

# Copia package.json e package-lock.json, poi installa le dipendenze
COPY package*.json ./
RUN npm ci

# Copia tutto il resto del codice sorgente
COPY . .

# Imposta la variabile d'ambiente per l'URL dell'API
ARG VITE_API_URL=http://localhost:8000
ENV VITE_API_URL=$VITE_API_URL

# Esegue il build del progetto
RUN npm run build

FROM nginx:stable-alpine

# Copia i file buildati al percorso di nginx e la configurazione di nginx
COPY --from=builder /app/dist /usr/share/nginx/html
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Espone la porta 80 per il traffico HTTP
EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
