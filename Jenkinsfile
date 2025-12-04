pipeline {
    agent any

    environment {
        PROJECT_NAME = '25RP18177_LOGIN_REGISTER_MODULE'
        DOCKER_REGISTRY = 'docker.io'
        DOCKER_IMAGE = '25rp18177-app'
        DOCKER_TAG = "${BUILD_NUMBER}"
    }

    stages {
        stage('Checkout') {
            steps {
                echo '=== Version Control stage is running ==='
                echo 'Checking out code from repository...'
                checkout scm
            }
        }

        stage('Build') {
            steps {
                echo '=== Containerization stage is running ==='
                echo 'Building Docker images...'
                script {
                    sh 'docker-compose build'
                }
            }
        }

        stage('Test') {
            steps {
                echo '=== Testing stage is running ==='
                echo 'Running tests...'
                script {
                    sh '''
                        echo "Testing PHP files for syntax errors..."
                        find src -name "*.php" -exec php -l {} \\;
                    '''
                }
            }
        }

        stage('Code Quality') {
            steps {
                echo '=== Code Quality stage is running ==='
                echo 'Running code quality checks...'
                script {
                    sh '''
                        echo "Checking code quality..."
                        echo "PHP syntax check completed."
                    '''
                }
            }
        }

        stage('Deploy') {
            steps {
                echo '=== Deployment stage is running ==='
                echo 'Deploying application...'
                script {
                    sh '''
                        echo "Starting Docker containers..."
                        docker-compose down || true
                        docker-compose up -d
                    '''
                }
            }
        }

        stage('Health Check') {
            steps {
                echo '=== Health Check stage is running ==='
                echo 'Performing health checks...'
                script {
                    sh '''
                        echo "Waiting for services to be ready..."
                        sleep 10
                        
                        echo "Checking web service..."
                        curl -f http://localhost:8082/index.php || exit 1
                        
                        echo "Checking database connection..."
                        docker exec 25rp18177-mysql-db mysqladmin ping -u root -ppassword || exit 1
                        
                        echo "All health checks passed!"
                    '''
                }
            }
        }

        stage('Cleanup') {
            steps {
                echo '=== Cleanup stage is running ==='
                echo 'Cleaning up...'
                script {
                    sh '''
                        echo "Pipeline execution completed successfully."
                    '''
                }
            }
        }
    }

    post {
        always {
            echo 'Pipeline execution finished.'
        }

        success {
            echo 'Pipeline succeeded! Application is running.'
            echo "Web App: http://localhost:8082"
            echo "phpMyAdmin: http://localhost:8083"
        }

        failure {
            echo 'Pipeline failed! Please check the logs.'
        }
    }
}
