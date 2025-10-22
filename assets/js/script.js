document.addEventListener('DOMContentLoaded', function(){
    // scroll messages-area to bottom if present
    const ma = document.querySelector('.messages-area');
    if (ma) ma.scrollTop = ma.scrollHeight;
  });

