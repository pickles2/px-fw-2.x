// パブリッシュを完全な同時に2つキックしたときに、
// 2重に走ってしまわないことを確認するためのスクリプト
const { spawn } = require('child_process');
const process1 = spawn('php', [
	__dirname+'/px2/.px_execute.php',
	'/?PX=publish.run'
]);
const process2 = spawn('php', [
	__dirname+'/px2/.px_execute.php',
	'/?PX=publish.run'
]);

// process1
process1.stdout.on('data', (data) => {
	console.log('1: ' + data.toString());
});
process1.stderr.on('data', (data) => {
	console.log('1: ' + data.toString());
});
process1.on('exit', (code) => {
	console.log('1: ' + `Child exited with code ${code}`);
});

// process2
process2.stdout.on('data', (data) => {
	console.log('2: ' + data.toString());
});
process2.stderr.on('data', (data) => {
	console.log('2: ' + data.toString());
});
process2.on('exit', (code) => {
	console.log('2: ' + `Child exited with code ${code}`);
});
