 /* exported trace */
function goBack() {
    window.history.back();
}

// Logging utility function.
function trace(arg) {
  var now = (window.performance.now() / 1000).toFixed(3);
  console.log(now + ': ', arg);
}