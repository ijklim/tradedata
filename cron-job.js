// This script replaces the need to set up the following cron job:
// * * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1
// The command to run this script: node cron-job.js
const exec = require('child_process').exec;

var everyMinute = function () {
  console.log((new Date()).toTimeString() + ':');
  exec('php artisan schedule:run', (error, stdout, stderr) => {
      console.log(`${stdout}`);
      console.log(`${stderr}`);
      if (error !== null) {
          console.log(`exec error: ${error}`);
      }
  });
}

var startScheduler = function () {
    everyMinute();
    setInterval(everyMinute, 1000 * 60);
}

// Scheduler must run every minute starting from hh:mm:00
setTimeout(startScheduler, 1000 * (60 - (new Date()).getSeconds()));

console.log('Current Time: ' + (new Date()).toTimeString());