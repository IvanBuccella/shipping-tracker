# Shipping Tracker

This is a PHP software that reads a tracking number and, using the Amazon Track API, produces in output a JSON that contains the tracking history.

## Tutorial Structure

- **[Installation](#installation)**
  - **[Prerequisites](#prerequisites)**
  - **[Repository](#repository)**
  - **[Environment Variables](#environment-variables)**
  - **[Build](#build)**
  - **[Run Docker Services](#run-docker-services)**

## Installation

### Prerequisites

- Docker and Docker Compose (Application containers engine). Install it from here https://www.docker.com

### Repository

Clone the repository:

```sh
$ git clone https://github.com/IvanBuccella/shipping-tracker
```

### Environment Variables

Set your own environment variables by using the `.env-sample` file.

### Build

Build the local environment with Docker:

```sh
$ docker-compose build
```

### Run Docker Services

```sh
$ docker-compose up
```

You can see the JSON by visiting the web page `http://localhost:{FE_PORT}/?tracking-code={YOUR TRACKING CODE}`.

### Enjoy :-)
