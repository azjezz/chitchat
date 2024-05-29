# ChitChat

A simple live chat application built using Neutomic.

## Getting Started

To get started, clone this repository and run the following command:

```bash
compser install
```

This will install all the dependencies required to run the project.

## Running the Project

To run the project, run the following command:

```bash
php src/main.php http:server:cluster
```

Run the project in production mode by running the following command:

```bash
PROJECT_MODE=production PROJECT_DEBUG=0 php src/main.php http:server:cluster
```

You can now access the project at `http://localhost:8080`.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
