FROM node:23


WORKDIR /usr/src
COPY package.json package-lock.json ./
RUN npm install
RUN ln -s /usr/src/node_modules/.bin/babel /usr/local/bin/babel

COPY babel.config.json ./

