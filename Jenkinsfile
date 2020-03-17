pipeline {
  agent any

  stages {
    stage('SCM') {
      steps {
        checkout scm
      }
    }

    stage('SonarQube analysis') {
      tools {
        tool 'SonarScanner'
      }

      steps {
        withSonarQubeEnv() {
          sh "sonar-scanner"
        }
      }
    }
  }
}
