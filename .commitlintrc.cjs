module.exports = {
  extends: ['@commitlint/config-conventional'],
  rules: {
    'type-enum': [
      2,
      'always',
      [
        'feat',     // new feature
        'fix',      // bug fix
        'docs',     // documentation
        'style',    // formatting, missing semi-colons, etc
        'refactor', // code change that neither fixes a bug nor adds a feature
        'perf',     // performance improvements
        'test',     // adding missing tests
        'chore',    // maintain, dependencies, etc
        'ci',       // CI/CD changes
        'build',    // build system changes
        'revert'    // revert previous commit
      ]
    ],
    'subject-max-length': [2, 'always', 200],
    'body-max-line-length': [2, 'always', 200]
  }
};
