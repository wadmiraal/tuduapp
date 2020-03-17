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
        hudson.plugins.sonar.SonarRunnerInstallation 'SonarScanner'
      }

      steps {
        withSonarQubeEnv() {
          sh "sonar-scanner"
        }
      }
    }
  }
}
