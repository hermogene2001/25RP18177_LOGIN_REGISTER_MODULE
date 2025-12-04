# Jenkins Pipeline Setup Guide with Git SCM

## Overview
This guide demonstrates how to create and test a Jenkins pipeline using Git as the Source Control Management (SCM) system for the 25RP18177_LOGIN_REGISTER_MODULE project.

---

## Prerequisites

1. **Jenkins Installation**
   - Jenkins Server running (version 2.350 or higher)
   - Jenkins accessible at `http://localhost:8080` (or your Jenkins URL)
   - Administrator access to Jenkins

2. **Required Jenkins Plugins**
   - Pipeline (workflow-aggregator)
   - Git (git)
   - Docker Pipeline (docker-workflow)
   - Blue Ocean (blueocean)

3. **System Requirements**
   - Git installed on Jenkins server
   - Docker and Docker Compose installed
   - PHP CLI for syntax checking
   - cURL for health checks

4. **GitHub Repository**
   - Repository URL: `https://github.com/hermogene2001/25RP18177_LOGIN_REGISTER_MODULE.git`
   - Public repository or credentials configured in Jenkins

---

## Step 1: Install Required Jenkins Plugins

### Via Jenkins UI:
1. Go to **Manage Jenkins** → **Manage Plugins**
2. Go to **Available** tab
3. Search and install:
   - **Pipeline** (workflow-aggregator)
   - **Git** (git)
   - **Docker Pipeline** (docker-workflow)
   - **Blue Ocean** (blueocean)
4. Restart Jenkins

### Via Jenkins CLI:
```bash
java -jar jenkins-cli.jar -s http://localhost:8080 install-plugin \
  workflow-aggregator git docker-workflow blueocean
```

---

## Step 2: Create a New Pipeline Job

### Method 1: Using Jenkins Web UI

1. **Open Jenkins**
   - Navigate to `http://localhost:8080`
   - Click **+ New Item**

2. **Configure Job Details**
   - **Job name**: `25RP18177-CI-CD-Pipeline`
   - **Type**: Select **Pipeline**
   - Click **OK**

3. **Configure Pipeline Settings**
   - Go to **Build Triggers** section
   - Check **GitHub hook trigger for GITScm polling**
   - Check **Poll SCM** and set schedule: `H/15 * * * *` (every 15 minutes)

4. **Configure Pipeline Definition**
   - **Definition**: Select **Pipeline script from SCM**
   - **SCM**: Select **Git**

5. **Configure Git Repository**
   - **Repository URL**: `https://github.com/hermogene2001/25RP18177_LOGIN_REGISTER_MODULE.git`
   - **Credentials**: 
     - Select **+ Add** → **Jenkins** if needed
     - Choose **Username with password** or **SSH Key**
     - Enter your GitHub credentials
   - **Branch Specifier**: `*/main`
   - **Script Path**: `Jenkinsfile`

6. Click **Save**

### Method 2: Using Jenkins Declarative Pipeline (Alternative)

Create a new Pipeline job and use this configuration in the Pipeline section:

```groovy
pipeline {
    agent any
    
    options {
        buildDiscarder(logRotator(numToKeepStr: '10'))
        timestamps()
    }
    
    triggers {
        githubPush()
        pollSCM('H/15 * * * *')
    }
    
    stages {
        stage('SCM Checkout') {
            steps {
                git branch: 'main',
                    url: 'https://github.com/hermogene2001/25RP18177_LOGIN_REGISTER_MODULE.git'
                echo '=== SCM Checkout stage is running ==='
            }
        }
    }
}
```

---

## Step 3: Configure Git Credentials in Jenkins

### Option A: Username and Password

1. Go to **Manage Jenkins** → **Manage Credentials**
2. Click on **System** → **Global credentials**
3. Click **+ Add Credentials**
4. Fill in:
   - **Kind**: Username with password
   - **Scope**: Global
   - **Username**: `hermogene2001`
   - **Password**: Your GitHub Personal Access Token (PAT)
   - **ID**: `github-credentials`
   - **Description**: GitHub Credentials for 25RP18177
5. Click **Create**

### Option B: SSH Key

1. Generate SSH key if not already present:
```bash
ssh-keygen -t rsa -b 4096 -f ~/.ssh/jenkins_github -C "jenkins@25rp18177"
```

2. Add public key to GitHub:
   - Go to GitHub → Settings → SSH and GPG keys
   - Click **New SSH key**
   - Paste content of `~/.ssh/jenkins_github.pub`

3. In Jenkins:
   - Go to **Manage Jenkins** → **Manage Credentials**
   - Click **+ Add Credentials**
   - Fill in:
     - **Kind**: SSH Username with private key
     - **Scope**: Global
     - **Username**: `git`
     - **Private Key**: Paste content of `~/.ssh/jenkins_github`
     - **ID**: `github-ssh-key`

---

## Step 4: Test the Pipeline

### Manual Trigger

1. Go to your Jenkins job: `25RP18177-CI-CD-Pipeline`
2. Click **Build Now**
3. Click on the build number in **Build History**
4. Click **Console Output** to view logs

### Expected Console Output

```
Started by user admin
Running as SYSTEM
Building in workspace /var/jenkins_home/workspace/25RP18177-CI-CD-Pipeline

> git rev-parse --is-inside-work-tree # timeout=10
Fetching changes from the remote Git repository
> git config remote.origin.url https://github.com/hermogene2001/25RP18177_LOGIN_REGISTER_MODULE.git # timeout=10
Fetching upstream changes from https://github.com/hermogene2001/25RP18177_LOGIN_REGISTER_MODULE.git
> git --version # timeout=10
> git fetch --tags --progress https://github.com/hermogene2001/25RP18177_LOGIN_REGISTER_MODULE.git +refs/heads/*:refs/remotes/origin/*

...

=== Version Control stage is running ===
Checking out code from repository...
=== Containerization stage is running ===
Building Docker images...
=== Testing stage is running ===
Testing PHP files for syntax errors...
=== Code Quality stage is running ===
Running code quality checks...
=== Deployment stage is running ===
Deploying application...
=== Health Check stage is running ===
Performing health checks...
=== Cleanup stage is running ===
Pipeline execution completed successfully.

Finished: SUCCESS
```

---

## Step 5: Verify Pipeline Execution

### Check Build Logs
1. Click on build number
2. View **Console Output**
3. Verify all stages completed successfully

### Monitor Pipeline in Blue Ocean

1. Go to Jenkins home
2. Click **Open Blue Ocean** (in left sidebar)
3. Select `25RP18177-CI-CD-Pipeline`
4. View visual pipeline representation
5. Click on any stage to see detailed logs

### Verify Docker Services

```bash
# Check running containers
docker ps

# Expected output:
# 25rp18177-web-app      - PHP Apache web server (Port 8082)
# 25rp18177-mysql-db     - MySQL database (Port 3307)
# 25rp18177-phpmyadmin   - phpMyAdmin (Port 8083)

# Check application
curl -I http://localhost:8082/index.php

# Check database
docker exec 25rp18177-mysql-db mysqladmin ping -u root -ppassword
```

---

## Step 6: Configure GitHub Webhook (Optional but Recommended)

### Automatic Pipeline Trigger on Push

1. **In GitHub Repository**
   - Go to **Settings** → **Webhooks**
   - Click **Add webhook**
   - Fill in:
     - **Payload URL**: `http://your-jenkins-url/github-webhook/`
     - **Content type**: `application/json`
     - **Events**: `Push events` (or Just the push event)
   - Click **Add webhook**

2. **In Jenkins**
   - Go to job configuration
   - Under **Build Triggers**, ensure **GitHub hook trigger for GITScm polling** is checked
   - Save

3. **Test Webhook**
   - Go to GitHub webhook settings
   - Click the webhook → **Recent Deliveries**
   - Verify successful delivery (green checkmark)

---

## Step 7: Pipeline Execution Flow

### Complete Pipeline Stages

```
Code Push to GitHub
        ↓
GitHub Webhook Trigger (or manual trigger)
        ↓
Jenkins Detects Change
        ↓
[Stage 1] Version Control - SCM Checkout
        ↓
[Stage 2] Containerization - Build Docker Images
        ↓
[Stage 3] Testing - PHP Syntax Validation
        ↓
[Stage 4] Code Quality - Code Analysis
        ↓
[Stage 5] Deployment - Start Containers
        ↓
[Stage 6] Health Check - Service Verification
        ↓
[Stage 7] Cleanup - Post Actions
        ↓
Success/Failure Notification
```

---

## Step 8: Troubleshooting

### Common Issues

#### 1. Git Clone Fails
```
Error: Authentication failed
Solution:
- Verify GitHub credentials in Jenkins
- Check SSH keys or Personal Access Token
- Ensure repository is accessible
```

#### 2. Docker Build Fails
```
Error: Docker daemon not accessible
Solution:
- Verify Docker is installed on Jenkins server
- Add Jenkins user to docker group: sudo usermod -aG docker jenkins
- Restart Jenkins service
```

#### 3. Health Check Fails
```
Error: Connection refused on port 8082
Solution:
- Ensure ports 8082, 8083, 3307 are available
- Check Docker container status: docker ps
- View container logs: docker logs 25rp18177-web-app
```

#### 4. Permission Denied
```
Error: /var/run/docker.sock: Permission denied
Solution:
sudo chmod 666 /var/run/docker.sock
or
sudo usermod -aG docker jenkins
sudo systemctl restart jenkins
```

---

## Step 9: Advanced Configuration

### Pipeline Parameters

Add this to your Jenkinsfile for parameterized builds:

```groovy
pipeline {
    agent any
    
    parameters {
        choice(name: 'ENVIRONMENT', choices: ['dev', 'staging', 'prod'], description: 'Deployment environment')
        booleanParam(name: 'SKIP_TESTS', defaultValue: false, description: 'Skip testing stage')
    }
    
    stages {
        stage('Deploy') {
            steps {
                echo "Deploying to ${params.ENVIRONMENT}"
                script {
                    if (!params.SKIP_TESTS) {
                        echo "Running tests..."
                    }
                }
            }
        }
    }
}
```

### Email Notifications

Add to post section of Jenkinsfile:

```groovy
post {
    failure {
        emailext(
            subject: "Pipeline Failed: ${env.JOB_NAME} #${env.BUILD_NUMBER}",
            body: "Build failed. Check console output at ${env.BUILD_URL}",
            to: "your-email@example.com"
        )
    }
    success {
        emailext(
            subject: "Pipeline Success: ${env.JOB_NAME} #${env.BUILD_NUMBER}",
            body: "Build successful! Application deployed.",
            to: "your-email@example.com"
        )
    }
}
```

---

## Step 10: Verify Complete Setup

### Checklist

- [ ] Jenkins is running and accessible
- [ ] Required plugins are installed
- [ ] Git credentials are configured in Jenkins
- [ ] Pipeline job `25RP18177-CI-CD-Pipeline` is created
- [ ] Jenkinsfile is in repository main branch
- [ ] Pipeline definition points to correct repository and branch
- [ ] Build triggers are configured
- [ ] First manual build executed successfully
- [ ] All pipeline stages completed
- [ ] Docker services are running
- [ ] Web application accessible at http://localhost:8082
- [ ] Database is accessible
- [ ] phpMyAdmin accessible at http://localhost:8083

---

## Summary

This setup creates a complete CI/CD pipeline that:

1. **Automatically detects** code changes via Git SCM
2. **Builds** Docker containers with application code
3. **Tests** PHP syntax and code quality
4. **Deploys** services using docker-compose
5. **Verifies** application health
6. **Notifies** of success or failure

The pipeline is fully automated and can be triggered by:
- Manual build button click
- Git push (webhook)
- Scheduled polling
- Other pipeline jobs

---

## Related Resources

- Jenkinsfile: `./Jenkinsfile`
- Docker Compose: `./docker-compose.yml`
- Database Init: `./init.sql`
- DevOps Documentation: `./DEVOPS_STAGES.md`
- GitHub Repository: `https://github.com/hermogene2001/25RP18177_LOGIN_REGISTER_MODULE`
