pipeline {
  agent any

  stages {
    stage('SonarQube Analysis') {
      environment {
        SCANNER_HOME = tool 'SonarScanner'
      }

      steps {
        withSonarQubeEnv('') {
          sh "${SCANNER_HOME}/bin/sonar-scanner"
        }
      }
    }

    // Requires Webhook setup in SonarQube
    stage('SonarQube Quality Gate') {
      steps {
        timeout(time: 1, unit: 'HOURS') {
          waitForQualityGate abortPipeline: true
        }
      }
    }
  }
}
