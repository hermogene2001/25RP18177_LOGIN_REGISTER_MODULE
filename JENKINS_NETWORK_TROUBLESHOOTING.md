# Jenkins Network Connectivity - GitHub Access Troubleshooting

## Error Analysis

The error `Could not resolve host: github.com` indicates:
1. **DNS Resolution Failure** - Cannot resolve github.com domain name
2. **Network Connectivity Issue** - Jenkins server cannot reach github.com
3. **Firewall/Proxy Block** - Network traffic to GitHub is blocked
4. **Container Network Issue** - If Jenkins is in Docker, container network is misconfigured

---

## Quick Diagnosis

### Test Network Connectivity

```bash
# Check if you can reach github.com
ping github.com

# Check DNS resolution
nslookup github.com
dig github.com

# Test HTTPS connectivity
curl -I https://github.com

# Test git clone
git clone https://github.com/hermogene2001/25RP18177_LOGIN_REGISTER_MODULE.git /tmp/test
```

---

## Solutions

### Solution 1: Check DNS Configuration

#### For Linux/Ubuntu:

```bash
# Check current DNS settings
cat /etc/resolv.conf

# If DNS is not configured, add Google DNS
sudo nano /etc/resolv.conf

# Add these lines:
nameserver 8.8.8.8
nameserver 8.8.4.4

# Or use Cloudflare DNS:
nameserver 1.1.1.1
nameserver 1.0.0.1

# Save and exit (Ctrl+X, Y, Enter)

# Restart network service
sudo systemctl restart networking

# Test DNS
nslookup github.com
```

#### For macOS:

```bash
# Check DNS
scutil --dns

# Flush DNS cache
sudo dscacheutil -flushcache

# Add DNS servers via System Preferences:
# System Preferences → Network → Wi-Fi → Advanced → DNS
# Add: 8.8.8.8, 8.8.4.4
```

#### For Windows:

```bash
# Open Command Prompt as Administrator

# Check current DNS
ipconfig /all

# Flush DNS cache
ipconfig /flushdns

# Set new DNS
netsh interface ipv4 set dnsservers name="Ethernet" static 8.8.8.8 primary
netsh interface ipv4 add dnsservers name="Ethernet" 8.8.4.4 index=2
```

---

### Solution 2: Configure Jenkins Network Settings

#### If Jenkins is in Docker

**Option A: Fix Docker Network**

```bash
# Check Docker network status
docker network ls

# Inspect network
docker network inspect bridge

# If Docker container cannot reach outside:
docker run -it --rm alpine ping github.com

# If ping fails, the issue is Docker network configuration
```

**Option B: Use Host Network (Linux only)**

```bash
# Stop current Jenkins container
docker stop jenkins

# Run Jenkins with host network
docker run -d \
  --name jenkins \
  --network host \
  -v jenkins_home:/var/jenkins_home \
  -v /var/run/docker.sock:/var/run/docker.sock \
  jenkins/jenkins:lts-jdk11
```

**Option C: Specify DNS for Docker Container**

```bash
# Stop and remove container
docker stop jenkins
docker rm jenkins

# Run with explicit DNS
docker run -d \
  --name jenkins \
  -p 8080:8080 \
  -p 50000:50000 \
  --dns 8.8.8.8 \
  --dns 8.8.4.4 \
  -v jenkins_home:/var/jenkins_home \
  jenkins/jenkins:lts-jdk11
```

**Option D: Configure Docker Daemon**

Create/edit `/etc/docker/daemon.json`:

```json
{
  "dns": ["8.8.8.8", "8.8.4.4", "1.1.1.1"],
  "insecure-registries": [],
  "log-driver": "json-file",
  "log-opts": {
    "max-size": "10m",
    "max-file": "3"
  }
}
```

Restart Docker:
```bash
sudo systemctl restart docker
docker restart jenkins
```

---

### Solution 3: Check Firewall Rules

#### Ubuntu/Debian with UFW:

```bash
# Check firewall status
sudo ufw status

# Allow HTTPS traffic
sudo ufw allow 443/tcp

# Allow HTTP traffic
sudo ufw allow 80/tcp

# Reload firewall
sudo ufw reload

# Test connectivity
curl -I https://github.com
```

#### CentOS/RHEL with Firewalld:

```bash
# Check firewall status
sudo firewall-cmd --state

# Allow HTTPS
sudo firewall-cmd --permanent --add-service=https

# Allow HTTP
sudo firewall-cmd --permanent --add-service=http

# Reload firewall
sudo firewall-cmd --reload

# Test connectivity
curl -I https://github.com
```

#### Check iptables:

```bash
# View current rules
sudo iptables -L -n

# If blocking, allow HTTPS
sudo iptables -A OUTPUT -p tcp --dport 443 -j ACCEPT
sudo iptables -A OUTPUT -p tcp --dport 80 -j ACCEPT

# Save rules (depends on system)
sudo netfilter-persistent save  # Debian/Ubuntu
sudo service iptables save      # RHEL/CentOS
```

---

### Solution 4: Proxy Configuration

If accessing through a proxy server:

#### Configure Jenkins Proxy

1. Go to **Manage Jenkins** → **System Configuration**
2. Scroll to **Proxy Configuration** section
3. Check **Manual proxy configuration**
4. Set:
   - **HTTP Proxy Host**: Your proxy server
   - **HTTP Proxy Port**: Proxy port (usually 3128 or 8080)
   - **HTTPS Proxy Host**: Same as HTTP
   - **HTTPS Proxy Port**: Same as HTTP
   - **No Proxy Host**: `localhost,127.0.0.1,github.com`
5. If proxy requires authentication:
   - **Proxy User**: Your username
   - **Proxy Password**: Your password
6. Click **Save**

#### Configure Git Proxy

```bash
# Configure Git to use proxy
git config --global http.proxy http://proxy-server:port
git config --global https.proxy https://proxy-server:port

# With authentication
git config --global http.proxy http://username:password@proxy-server:port
git config --global https.proxy https://username:password@proxy-server:port

# Verify configuration
git config --global --list

# To remove proxy
git config --global --unset http.proxy
git config --global --unset https.proxy
```

---

### Solution 5: SSH Alternative (if HTTPS blocked)

If HTTPS is blocked but SSH is allowed:

#### Configure SSH Key in Jenkins

1. Generate SSH key (if not already done):
```bash
ssh-keygen -t rsa -b 4096 -f ~/.ssh/github_jenkins -C "jenkins@github"
```

2. Add public key to GitHub:
   - Go to GitHub Settings → SSH and GPG keys
   - Click **New SSH key**
   - Paste content of `~/.ssh/github_jenkins.pub`

3. Configure Jenkins credentials:
   - Go to **Manage Jenkins** → **Manage Credentials**
   - Click **Global credentials**
   - Click **+ Add Credentials**
   - **Kind**: SSH Username with private key
   - **Username**: `git`
   - **Private Key**: Paste content of `~/.ssh/github_jenkins`

4. Update Jenkins job:
   - Edit job configuration
   - Under **Pipeline** → **Definition**
   - Change Repository URL to SSH format:
   ```
   git@github.com:hermogene2001/25RP18177_LOGIN_REGISTER_MODULE.git
   ```
   - Select SSH credential
   - Save

#### Test SSH Connection

```bash
# Test SSH to GitHub
ssh -T git@github.com

# Should output:
# Hi hermogene2001! You've successfully authenticated, but GitHub does not provide shell access.
```

---

### Solution 6: Docker Compose Fix

If using Docker Compose, update your docker-compose.yml for better network connectivity:

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
    environment:
      - JENKINS_OPTS=--logfile=/var/log/jenkins/jenkins.log
    # Specify DNS for container
    dns:
      - 8.8.8.8
      - 8.8.4.4
    # Specify network
    networks:
      - jenkins-network
    restart: always

volumes:
  jenkins_home:

networks:
  jenkins-network:
    driver: bridge
    driver_opts:
      com.docker.network.driver.mtu: 1450
```

Then restart:
```bash
docker-compose down
docker-compose up -d
```

---

## Verification Steps

After applying a solution:

```bash
# 1. Test DNS resolution
nslookup github.com

# 2. Test HTTPS connectivity
curl -I https://github.com

# 3. Test Git access
git ls-remote https://github.com/hermogene2001/25RP18177_LOGIN_REGISTER_MODULE.git

# 4. If using SSH
ssh -T git@github.com

# 5. From Jenkins container (if using Docker)
docker exec jenkins curl -I https://github.com
docker exec jenkins nslookup github.com
```

---

## Step-by-Step Solution for Your Environment

### Quick Fix (Most Common):

```bash
# 1. Check current DNS
cat /etc/resolv.conf

# 2. Edit resolv.conf
sudo nano /etc/resolv.conf

# 3. Add Google DNS
nameserver 8.8.8.8
nameserver 8.8.4.4

# 4. Save and exit

# 5. Test connectivity
curl -I https://github.com

# 6. If in Docker, restart container
docker restart jenkins

# 7. Retry Jenkins build
```

### If Using Docker Jenkins:

```bash
# Stop Jenkins
docker stop jenkins

# Remove container
docker rm jenkins

# Run with DNS configuration
docker run -d \
  --name jenkins \
  -p 8080:8080 \
  -p 50000:50000 \
  --dns 8.8.8.8 \
  --dns 8.8.4.4 \
  -v jenkins_home:/var/jenkins_home \
  -v /var/run/docker.sock:/var/run/docker.sock \
  jenkins/jenkins:lts-jdk11

# Wait for startup
sleep 30

# Test connectivity from Jenkins container
docker exec jenkins curl -I https://github.com

# Retry pipeline build in Jenkins UI
```

---

## Advanced Debugging

### Enable Jenkins Git Debug Logs

In your Jenkinsfile:

```groovy
pipeline {
    agent any
    
    stages {
        stage('Debug Network') {
            steps {
                sh '''
                    echo "=== Network Diagnostics ==="
                    echo "DNS servers:"
                    cat /etc/resolv.conf
                    
                    echo "=== Testing DNS Resolution ==="
                    nslookup github.com || echo "nslookup failed"
                    
                    echo "=== Testing HTTPS Connectivity ==="
                    curl -I -v https://github.com || echo "curl failed"
                    
                    echo "=== Testing Git Access ==="
                    GIT_TRACE=1 git ls-remote https://github.com/hermogene2001/25RP18177_LOGIN_REGISTER_MODULE.git
                '''
            }
        }
    }
}
```

### Check Jenkins Environment

Go to Jenkins Script Console:
```groovy
def proc = "cat /etc/resolv.conf".execute()
proc.waitFor()
println(proc.text)
```

---

## Summary

| Issue | Solution |
|-------|----------|
| DNS not resolving | Add Google DNS (8.8.8.8) to `/etc/resolv.conf` |
| Firewall blocking | Open ports 80 (HTTP) and 443 (HTTPS) in firewall |
| Docker network issue | Use `--dns` flag or configure daemon.json |
| Behind proxy | Configure Jenkins proxy settings |
| HTTPS blocked, SSH available | Switch to SSH clone URL and SSH credentials |

**Most likely solution for your case:**
```bash
sudo nano /etc/resolv.conf
# Add: nameserver 8.8.8.8
# Save and exit
# Restart Jenkins
```

After applying the fix, retry your Jenkins pipeline build!
