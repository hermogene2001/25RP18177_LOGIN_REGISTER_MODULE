# Jenkins Git Error - Troubleshooting Guide

## Error Analysis

The error `Failed to exec spawn helper: error=0` and `Cannot run program "git"` indicates that:
1. Git is not installed on the Jenkins server
2. Git is installed but not in the system PATH
3. Jenkins container doesn't have Git executable
4. File permission issues on Git binary

---

## Solutions

### Solution 1: Install Git on Jenkins Server

#### For Ubuntu/Debian:
```bash
# Update package manager
sudo apt-get update

# Install Git
sudo apt-get install -y git

# Verify installation
which git
git --version
```

#### For CentOS/RHEL:
```bash
# Update package manager
sudo yum update -y

# Install Git
sudo yum install -y git

# Verify installation
which git
git --version
```

#### For macOS:
```bash
# Using Homebrew
brew install git

# Verify installation
which git
git --version
```

---

### Solution 2: If Using Jenkins Docker Container

If Jenkins is running in Docker, you need to install Git in the container.

#### Option A: Update Dockerfile for Jenkins Container

Create a custom Jenkins Docker image with Git included:

```dockerfile
FROM jenkins/jenkins:2.387.1

USER root

# Install Git and other tools
RUN apt-get update && \
    apt-get install -y git curl docker.io && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Add Jenkins user to docker group
RUN usermod -aG docker jenkins

USER jenkins
```

#### Option B: Install Git in Running Container

If Jenkins is already running in Docker:

```bash
# Access the Jenkins container
docker exec -u root -it <jenkins-container-id> bash

# Update and install Git
apt-get update
apt-get install -y git

# Exit container
exit
```

#### Option C: Use Jenkins Docker Image with Git Pre-installed

```bash
# Stop current Jenkins container
docker stop jenkins

# Remove current container
docker rm jenkins

# Run Jenkins with Git already included
docker run -d \
  --name jenkins \
  -p 8080:8080 \
  -p 50000:50000 \
  -v jenkins_home:/var/jenkins_home \
  -v /var/run/docker.sock:/var/run/docker.sock \
  jenkins/jenkins:lts-jdk11
```

Then install Git in the running container:

```bash
docker exec -u root -it jenkins bash

apt-get update && apt-get install -y git

exit
```

---

### Solution 3: Configure Jenkins Git Plugin

1. **Go to Jenkins Dashboard**
   - Navigate to `http://localhost:8080`
   - Click **Manage Jenkins**

2. **Configure Tools**
   - Click **Global Tool Configuration**
   - Find **Git** section
   - If Git is not auto-detected, click **Add Git**
   - Set **Name**: `Default`
   - Set **Path to Git executable**: `/usr/bin/git` (or output of `which git`)
   - Click **Save**

3. **Verify Git Configuration**
   - Go to **Manage Jenkins** → **Global Tool Configuration**
   - Scroll to **Git**
   - Click **Test Git** button (if available)

---

### Solution 4: Fix File Permissions

```bash
# Ensure Git binary is executable
sudo chmod +x /usr/bin/git

# Verify Jenkins can access git
sudo -u jenkins /usr/bin/git --version
```

---

## Complete Docker Solution for Jenkins

If running Jenkins in Docker, here's the complete setup:

### docker-compose.yml for Jenkins

```yaml
version: '3.8'

services:
  jenkins:
    image: jenkins/jenkins:lts-jdk11
    container_name: jenkins-server
    ports:
      - "8080:8080"
      - "50000:50000"
    volumes:
      - jenkins_home:/var/jenkins_home
      - /var/run/docker.sock:/var/run/docker.sock
      - /usr/bin/docker:/usr/bin/docker
    environment:
      - JENKINS_OPTS=--logfile=/var/log/jenkins/jenkins.log
    build:
      context: .
      dockerfile: Dockerfile.jenkins
    networks:
      - jenkins-network
    restart: always

volumes:
  jenkins_home:

networks:
  jenkins-network:
    driver: bridge
```

### Dockerfile.jenkins

```dockerfile
FROM jenkins/jenkins:lts-jdk11

USER root

# Install Git, Docker CLI, and other tools
RUN apt-get update && \
    apt-get install -y \
    git \
    curl \
    wget \
    docker.io \
    docker-compose \
    php-cli \
    mysql-client && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Add Jenkins user to docker group
RUN usermod -aG docker jenkins

USER jenkins
```

### Start Jenkins with Docker

```bash
# Navigate to project directory
cd /home/hermogene/25RP18177_LOGIN_REGISTER_MODULE

# Create Dockerfile.jenkins with content above
# Create docker-compose.yml with content above

# Start Jenkins
docker-compose up -d

# Wait for Jenkins to start (30-60 seconds)
sleep 60

# Check Jenkins logs
docker-compose logs -f jenkins

# Access Jenkins
# URL: http://localhost:8080
```

---

## Verify Git is Available to Jenkins

### Method 1: Via Jenkins Script Console

1. Go to **Manage Jenkins** → **Script Console**
2. Paste this Groovy code:

```groovy
def proc = "which git".execute()
proc.waitFor()
println(proc.text)
println("Exit code: " + proc.exitValue())
```

3. Click **Run**
4. Should output the path to git (e.g., `/usr/bin/git`)

### Method 2: Via Pipeline Stage

Create a test pipeline job with this content:

```groovy
pipeline {
    agent any
    
    stages {
        stage('Test Git') {
            steps {
                sh '''
                    echo "Testing Git availability..."
                    which git
                    git --version
                '''
            }
        }
    }
}
```

Run the job and check console output.

---

## Recommended Quick Fix for Your Setup

Since you're running the application in Docker containers, I recommend:

### Step 1: Install Git in Jenkins Container (if using Docker)

```bash
# If Jenkins is in Docker
docker exec -u root -it <jenkins-container-id> apt-get update
docker exec -u root -it <jenkins-container-id> apt-get install -y git
```

Or if Jenkins is on the host machine:

```bash
# For Linux
sudo apt-get update
sudo apt-get install -y git

# Verify
git --version
```

### Step 2: Restart Jenkins

```bash
# If using Docker
docker restart <jenkins-container-id>

# If running as service
sudo systemctl restart jenkins

# If running standalone
# Stop and start Jenkins application
```

### Step 3: Retry Pipeline Build

1. Go to Jenkins job: `25RP18177-CI-CD-Pipeline`
2. Click **Build Now**
3. Monitor console output
4. Should now successfully clone from Git

---

## Verification Steps

After applying the fix:

```bash
# 1. Verify Git is installed
git --version

# 2. Verify Git is in PATH
which git

# 3. Test Git clone manually
git clone https://github.com/hermogene2001/25RP18177_LOGIN_REGISTER_MODULE.git /tmp/test-clone

# 4. Check Jenkins can access it
sudo -u jenkins git --version  # If Jenkins runs as jenkins user
```

---

## Alternative: Use Jenkins without Git SCM

If you want to bypass the Git issue temporarily, you can use Jenkins with inline Jenkinsfile:

1. In Jenkins job configuration
2. **Pipeline** section
3. **Definition**: Select **Pipeline script** (instead of "Pipeline script from SCM")
4. Paste the Jenkinsfile content directly
5. Click **Save**

However, this is **NOT recommended** for production as you lose the GitOps benefits.

---

## Summary

The issue is that **Git is not installed on your Jenkins server**. 

**Quick Fix:**
```bash
# Ubuntu/Debian
sudo apt-get update && sudo apt-get install -y git

# Restart Jenkins
sudo systemctl restart jenkins

# Run pipeline again
```

**For Docker Jenkins:**
```bash
docker exec -u root -it jenkins apt-get update
docker exec -u root -it jenkins apt-get install -y git
docker restart jenkins
```

After installation, retry your pipeline build and it should work successfully!
