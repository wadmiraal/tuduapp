pipeline {
  agent any

  stages {
    stage('SCM') {
      steps {
        checkout scm
      }
    }

    stage('SonarQube analysis') {
      steps {
        withSonarQubeEnv() {
          def scannerHome = tool 'SonarScanner';
          sh "${scannerHome}/bin/sonar-scanner"
        }
      }
    }
  }
}
