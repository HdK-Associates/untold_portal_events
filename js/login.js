(function(){
    const loginForm = document.querySelector('#login_form');
    if(!loginForm) return;
    const errorBox = loginForm.querySelector('#login_errors');
    loginForm.addEventListener('submit', function(e){
        e.preventDefault();
        errorBox.innerHTML = '';
        loginForm.classList.add('loading');
        const formData = new FormData(loginForm);
        
        // Convert FormData to JSON
        const jsonData = {};
        for (let [key, value] of formData.entries()) {
            jsonData[key] = value;
        }
        fetch(loginForm.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(jsonData),
        })
        .then(response => response.json())
        .then(data => {
            if(data.hasOwnProperty('errorCode')){
                errorBox.innerHTML = `<p class="error">Either your email or password is incorrect.</p>`;
                loginForm.classList.remove('loading');
            }
            else{
                let emitEvent = new CustomEvent('spektrixLoginSuccess',{bubbles:true,detail:data});
                loginForm.dispatchEvent(emitEvent);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            loginForm.classList.remove('loading');
        });


    });
})();