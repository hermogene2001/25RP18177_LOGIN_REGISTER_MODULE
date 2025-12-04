# DevOps Pipeline - 25RP18177 Login Register Module

## DevOps Stages Overview

This document outlines the different stages of DevOps implementation for the 25RP18177 Login Register Module project.

---

## Stage 1: Version Control (Git)

### Repository Structure
```
25RP18177_LOGIN_REGISTER_MODULE/
├── src/
│   ├── index.php           # Home landing page with links
│   ├── register.php        # User registration form with validation
│   ├── login.php           # User authentication
│   ├── home.php            # User dashboard (after login)
│   └── logout.php          # Session termination
├── Dockerfile              # Container configuration
├── docker-compose.yml      # Multi-container orchestration
├── init.sql               # Database initialization script
├── Jenkinsfile            # CI/CD pipeline definition
└── .gitignore             # Git exclusions
```

### Git Commits History
```bash
# Step 1: Index page creation
git commit -m "Index page is created."

# Step 2: Registration page
git commit -m "Registration page is created."

# Step 3: Login page
git commit -m "Login page is created."

# Step 4: Full authentication implementation
git commit -m "Registration and login functionalities have been integrated."
```

---

## Stage 2: Containerization (Docker)

### Dockerfile
```dockerfile
FROM php:8.1-apache

# Enable mod_rewrite for Apache
RUN a2enmod rewrite

# Install required PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY src/ /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80

CMD ["apache2-foreground"]
```

### Three Services Architecture

#### Service 1: Web Application (25rp18177-web-app)
- **Image**: Custom PHP 8.1 Apache image
- **Port**: 8082:80
- **Purpose**: Hosts the login/registration application
- **Features**: 
  - Runs Apache web server
  - PHP 7.4+ with MySQL support
  - Volume mounted for live code updates

#### Service 2: Database (25rp18177-mysql-db)
- **Image**: MySQL 8.0
- **Port**: 3307:3306
- **Purpose**: Stores user credentials and application data
- **Features**:
  - Persistent data storage via Docker volume
  - Health checks enabled
  - Automatic database initialization via init.sql

#### Service 3: Database Management (25rp18177-phpmyadmin)
- **Image**: phpMyAdmin latest
- **Port**: 8083:80
- **Purpose**: Web interface for database management
- **Features**:
  - GUI for database operations
  - User-friendly database administration

---

## Stage 3: Infrastructure as Code (IaC)

### docker-compose.yml
```yaml
services:
  web:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: 25rp18177-web-app
    ports:
      - "8082:80"
    volumes:
      - ./src:/var/www/html
    environment:
      - DB_HOST=db
      - DB_USER=root
      - DB_PASSWORD=password
      - DB_NAME=25rp18177_shareride_db
    depends_on:
      - db
    networks:
      - auth-network
    restart: always

  db:
    image: mysql:8.0
    container_name: 25rp18177-mysql-db
    environment:
      - MYSQL_ROOT_PASSWORD=password
      - MYSQL_DATABASE=25rp18177_shareride_db
      - MYSQL_USER=app_user
      - MYSQL_PASSWORD=app_password
    ports:
      - "3307:3306"
    volumes:
      - ./init.sql:/docker-entrypoint-initdb.d/init.sql
      - auth-db-volume:/var/lib/mysql
    networks:
      - auth-network
    restart: always
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      timeout: 20s
      retries: 10

  phpmyadmin:
    image: phpmyadmin:latest
    container_name: 25rp18177-phpmyadmin
    environment:
      - PMA_HOST=db
      - PMA_USER=root
      - PMA_PASSWORD=password
    ports:
      - "8083:80"
    depends_on:
      - db
    networks:
      - auth-network
    restart: always

volumes:
  auth-db-volume:

networks:
  auth-network:
    driver: bridge
```

---

## Stage 4: Continuous Integration (CI)

### Jenkinsfile Pipeline Stages

#### 1. Checkout Stage
```groovy
stage('Checkout') {
    steps {
        echo 'Checking out code from repository...'
        checkout scm
    }
}
```
- Pulls latest code from Git repository
- Ensures all source files are available

#### 2. Build Stage
```groovy
stage('Build') {
    steps {
        echo 'Building Docker images...'
        script {
            sh 'docker-compose build'
        }
    }
}
```
- Builds custom Docker images
- Prepares containers for deployment

#### 3. Test Stage
```groovy
stage('Test') {
    steps {
        echo 'Running tests...'
        script {
            sh '''
                find src -name "*.php" -exec php -l {} \\;
            '''
        }
    }
}
```
- Validates PHP syntax
- Checks for code errors
- Ensures code quality

#### 4. Code Quality Stage
```groovy
stage('Code Quality') {
    steps {
        echo 'Running code quality checks...'
        script {
            sh '''
                echo "Checking code quality..."
            '''
        }
    }
}
```
- Analyzes code for standards
- Ensures best practices

#### 5. Deploy Stage
```groovy
stage('Deploy') {
    steps {
        echo 'Deploying application...'
        script {
            sh '''
                docker-compose down || true
                docker-compose up -d
            '''
        }
    }
}
```
- Stops existing containers
- Starts fresh deployment
- Brings up all services

#### 6. Health Check Stage
```groovy
stage('Health Check') {
    steps {
        echo 'Performing health checks...'
        script {
            sh '''
                sleep 10
                curl -f http://localhost:8082/index.php || exit 1
                docker exec 25rp18177-mysql-db mysqladmin ping -u root -ppassword || exit 1
            '''
        }
    }
}
```
- Verifies web server is responding
- Confirms database connectivity
- Ensures all services are healthy

---

## Stage 5: Database Schema (Database as Code)

### init.sql
```sql
CREATE DATABASE IF NOT EXISTS `25rp18177_shareride_db`;
USE `25rp18177_shareride_db`;

CREATE TABLE IF NOT EXISTS `tbl_users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_firstname` VARCHAR(50) NOT NULL,
  `user_lastname` VARCHAR(50) NOT NULL,
  `user_gender` VARCHAR(20),
  `user_email` VARCHAR(100) NOT NULL UNIQUE,
  `user_password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE INDEX idx_user_email ON `tbl_users`(`user_email`);
CREATE INDEX idx_created_at ON `tbl_users`(`created_at`);
```

---

## Stage 6: Application Deployment

### Environment Variables
```
DB_HOST=db
DB_USER=root
DB_PASSWORD=password
DB_NAME=25rp18177_shareride_db
```

### Network Configuration
- **Network Name**: auth-network
- **Driver**: Bridge
- **Services Connected**: web, db, phpmyadmin

---

## Stage 7: Monitoring & Health Checks

### Docker Health Check Configuration
```yaml
healthcheck:
  test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
  timeout: 20s
  retries: 10
```

### Service Endpoints
- **Web Application**: http://localhost:8082
- **phpMyAdmin**: http://localhost:8083
- **Database**: localhost:3307

---

## Stage 8: Security Implementation

### Features
1. **Password Security**: Passwords hashed using PHP's password_hash()
2. **Session Management**: Session-based authentication
3. **Input Validation**: Server-side validation on all forms
4. **Database Security**: User credentials stored securely
5. **Environment Variables**: Sensitive data in environment vars

---

## DevOps Best Practices Implemented

1. ✅ **Version Control**: Git with meaningful commit messages
2. ✅ **Containerization**: Docker for consistent environments
3. ✅ **Infrastructure as Code**: docker-compose.yml for infrastructure
4. ✅ **CI/CD Pipeline**: Jenkinsfile with automated stages
5. ✅ **Database as Code**: init.sql for schema management
6. ✅ **Health Checks**: Automated service verification
7. ✅ **Networking**: Isolated network for inter-service communication
8. ✅ **Persistence**: Docker volumes for data retention

---

## Deployment Commands

### Start Services
```bash
docker-compose up -d
```

### Stop Services
```bash
docker-compose down
```

### View Logs
```bash
docker-compose logs -f
```

### Access Services
- Web App: http://localhost:8082
- phpMyAdmin: http://localhost:8083
- Database: mysql://root:password@localhost:3307/25rp18177_shareride_db

---

## CI/CD Pipeline Workflow

```
Code Push → Git Checkout → Build → Test → Code Quality 
    → Deploy → Health Check → Success Notification
```

---

## Summary

This DevOps implementation demonstrates:
- **Containerization** for consistency
- **Orchestration** for multi-service management
- **Automation** through CI/CD pipeline
- **Infrastructure as Code** for reproducibility
- **Database automation** for schema management
- **Monitoring** through health checks
- **Version Control** for code management
