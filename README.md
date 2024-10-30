## Il Maestro

This project is a web-based application built with Laravel and Vue designed to simplify the management of multiple Kubernetes stations, with a special focus on K3S clusters. Developed as a collaborative effort by **José Areia** and **Bruno Santos**, `Il Maestro` enables administrators to efficiently monitor and control multiple Kubernetes environments from a centralized interface.

### Project Purpose

The main goal of `Il Maestro` is to provide an intuitive and robust solution for managing Kubernetes deployments, particularly optimized for K3S – a lightweight Kubernetes distribution ideal for edge computing, IoT, and resource-constrained environments. This application streamlines the management tasks, making it easier for users to:

- Monitor the status and performance of multiple K3S clusters.
- Deploy and manage applications across different Kubernetes environments.
- Configure and adjust cluster settings to suit specific use cases.
- Securely access and control Kubernetes nodes with an easy-to-use web interface.

### Key Features

- **Multi-Cluster Management**: Manage multiple Kubernetes clusters from a single dashboard.
- **K3S Focus**: Specialized support for K3S clusters, including lightweight deployment and configuration options.
- **User-Friendly Interface**: A modern, responsive UI built with Vue for a seamless user experience.
- **Scalable and Extensible**: Built with Laravel, providing a flexible backend for scalable deployments.
- **Secure Access**: Authentication and authorization mechanisms to ensure secure access to cluster resources.

### Technologies Used

- **Backend**: Laravel
- **Frontend**: Vue.js
- **Container** Orchestration: Kubernetes (K3S)
- **Database**: MySQL

### Installation

**Prerequisites**

- PHP 8.0 or higher
- Node.js and npm
- Kubernetes clusters (preferably K3S)

**Steps**

1. **Clone the repository:**
```
$ git clone https://github.com/your-repo/il-maestro.git
$ cd il-maestro
```

2. **Install dependecies:**
```
$ composer install
$ npm install && npm run dev
```

3. **Setup environment**: Copy `.env.example` to `.env` and update the necessary configurations.

4. **Run migrations**:
```
$ php artisan migrate
```

5. **Start the application**:
```
$ php artisan serve
```

### Usage

Once installed, `Il Maestro` provides a dashboard to add and manage Kubernetes clusters, deploy applications, and perform other cluster management tasks.

### Contributors

- José Areia
- Bruno Santos







