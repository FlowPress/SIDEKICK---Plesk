var gulp = require('gulp');
var watch = require('gulp-watch');
var zip = require('gulp-zip');
var fs = require('fs');
var clean = require('gulp-clean');
var GulpSSH = require('gulp-ssh');

var config = {
    host: 'localhost.localdomain',
    port: 22,
    username: 'root',
    privateKey: fs.readFileSync('/Users/bartdabek/.ssh/id_rsa')
}

var gulpSSH = new GulpSSH({
    ignoreErrors: false,
    sshConfig: config
})


gulp.task('install_ext', ['build'], function() {
    return gulpSSH
        .shell(['/usr/local/psa/bin/extension -u sidekick ; /usr/local/psa/bin/extension -i /mnt/hgfs/dist/sidekick_plesk_extension.zip'], {
            filePath: 'shell.log'
        })
        .pipe(gulp.dest('logs'))
});


gulp.task('clean', function() {
    return gulp.src('dist/**', {
            read: false
        })
        .pipe(clean());
});

gulp.task('build', ['clean'], function() {
    return gulp.src(['**/*',
            '!node_modules{,/**}',
            '!gulpfile.js',
            '!logs{,/**}',
            '!logs'
        ])
        .pipe(zip('sidekick_plesk_extension.zip'))
        .pipe(gulp.dest('dist'));
});

gulp.task('watch', function() {
    gulp.watch('**/*.php', ['install_ext'])
    gulp.watch('**/*.phtml', ['install_ext']);
});


gulp.task('default', ['watch', 'install_ext']);
