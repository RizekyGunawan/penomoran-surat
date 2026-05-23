pipeline {
    agent any

    environment {
        SERVER_IP   = "192.168.10.133"
        SERVER_USER = "digi"
        APP_DIR     = "/home/digi/srv/penomoran-surat"
        BRANCH      = "jeki"
    }

    stages {
        stage('Deploy to Server') {
            steps {
                sshagent(credentials: ['ams-ssh']) {
                    sh """
                        ssh -o StrictHostKeyChecking=no ${SERVER_USER}@${SERVER_IP} bash -s <<EOF

set -euo pipefail

echo "[JENKINS] 🚀 Deploy started at \$(date)"

cd ${APP_DIR}

chmod +x deploy.sh
./deploy.sh deploy ${BRANCH}

echo "[JENKINS] 🎉 Deploy finished at \$(date)"
EOF
"""
                }
            }
        }
    }

    post {
        always {
            echo "Pipeline finished"
        }
        failure {
            echo "❌ Deploy FAILED! Cek log."
        }
    }
}
