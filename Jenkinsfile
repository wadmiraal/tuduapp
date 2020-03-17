pipeline {
  agent any

  stages {
    stage('SCM') {
      steps {
        checkout scm
      }
    }

    stage('SonarQube analysis') {
      environment {
        SCANNER_HOME = tool 'SonarScanner'
      }

      steps {
        withSonarQubeEnv() {
          sh "${SCANNER_HOME}/bin/sonar-scanner"
        }
      }
    }
  }
}
