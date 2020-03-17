pipeline {
  agent any

  stages {
    stage('SonarQube Analysis') {
      environment {
        SCANNER_HOME = tool 'SonarScanner'
      }

      steps {
        // Name is really required... Empty string works, BUT will make waitForQualityGate below fail.
        withSonarQubeEnv('Local SQ') {
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
