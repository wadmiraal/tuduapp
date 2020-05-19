pipeline {
  agent any

  stages {
    stage('SonarQube Analysis') {
      environment {
        SCANNER_HOME = tool 'SonarScanner'
      }

      steps {
        withSonarQubeEnv() {
          sh "${SCANNER_HOME}/bin/sonar-scanner -foo"
        }
      }
    }
  }
}
