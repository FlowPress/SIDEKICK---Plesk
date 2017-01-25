/*

A00Y00-YPGN03-HWW988-H4R588-VG8T45
https://1d869043f690:8443/admin/module/catalog

docker-machine start
docker stop plesk; docker rm plesk
docker start plesk

docker run -d -it --name plesk -p 8880:8880 -p 80:80 -p 2121:21 -p 2222:22 -p 8443:8443 -p 8447:8447\
 -v /Users/bartdabek/Sites/sidekick/SIDEKICK---Plesk/:/mnt/sidekick \
 -v /Users/bartdabek/Sites/sidekick/SIDEKICK---Plesk/htdocs/:/usr/local/psa/admin/htdocs/modules/sidekick \
 -v /Users/bartdabek/Sites/sidekick/SIDEKICK---Plesk/plib/:/opt/psa/admin/plib/modules/sidekick \
 plesk/plesk:preview



docker exec -it plesk /usr/local/psa/bin/extension -i /mnt/sidekick/dist/sidekick_plesk_extension.zip && \
docker exec -it plesk rm -fr /usr/local/psa/admin/htdocs/modules/sidekick && \
docker exec -it plesk ln -s /usr/local/psa/admin/htdocs/modules/sidekick /usr/local/psa/admin/htdocs/modules/sidekick && \
docker exec -it plesk rm -fr /opt/psa/admin/plib/modules/sidekick && \
docker exec -it plesk ln -s /opt/psa/admin/plib/modules/sidekick2 /opt/psa/admin/plib/modules/sidekick


docker exec -it plesk rm -fr /opt/psa/admin/plib/modules/sidekick && \
docker exec -it plesk rm -fr /usr/local/psa/admin/htdocs/modules/sidekick && \
docker exec -it plesk /usr/local/psa/bin/extension -i /opt/psa/admin/plib/modules/sidekick2/dist/sidekick_plesk_extension.zip

dssh app
cd /usr/local/psa/admin/htdocs/modules/sidekick
cd /opt/psa/admin/plib/modules/

eval "$(docker-machine env default)"



*/

var gulp = require('gulp');
var watch = require('gulp-watch');
var zip = require('gulp-zip');
var fs = require('fs');
var clean = require('gulp-clean');
var GulpSSH = require('gulp-ssh');
var shell = require('gulp-shell')

var config = {
	host: 'localhost.localdomain',
	port: 22,
	username: 'root',
	privateKey: fs.readFileSync('/Users/bartdabek/.ssh/id_rsa')
}

var gulpSSH = new GulpSSH({ignoreErrors: false, sshConfig: config})

gulp.task('install_ext', ['build'], shell.task(['docker exec -it plesk /usr/local/psa/bin/extension -u Sidekick & sleep 5 & docker exec -it plesk /usr/local/psa/bin/extension -i /opt/psa/admin/plib/modules/sidekick2/dist/sidekick_plesk_extension.zip'], {
	verbose: true,
	ignoreErrors: true
}))

// gulp.task('install_ext', ['build'], function() {
//     return gulpSSH
//         .shell(['docker exec -it plesk /usr/local/psa/bin/extension -i /opt/psa/admin/plib/modules/sidekick2/dist/sidekick_plesk_extension.zip'], {
//             filePath: 'shell.log'
//         })
//         .pipe(gulp.dest('logs'))
// });

gulp.task('clean', function() {
	console.log('Clean');
	return gulp.src('dist/**', {read: false}).pipe(clean());
});

gulp.task('build', ['clean'], function() {
	return gulp.src(['**/*', '!node_modules{,/**}', '!gulpfile.js', '!logs{,/**}', '!logs']).pipe(zip('sidekick_plesk_extension.zip')).pipe(gulp.dest('dist'));
});

gulp.task('watch', function() {
	gulp.watch('**/*.php', ['install_ext'])
	gulp.watch('**/*.phtml', ['install_ext']);
});

gulp.task('default', ['watch', 'install_ext']);
