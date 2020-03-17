pipeline {
  agent any

  stages {
    stage('SonarQube analysis') {
      environment {
        SCANNER_HOME = tool 'SonarScanner'
      }

      steps {
        withSonarQubeEnv('Local SQ') {
          sh "${SCANNER_HOME}/bin/sonar-scanner"
        }
      }
    }
  }
}
