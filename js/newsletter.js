(function(){
    const newsletterForm = document.querySelector('#newsletter');
    const newsletterButton = document.querySelector('#newsletter_submit');
    const newsletterMessage = document.querySelector('.sign-up_message');
    if(newsletterForm){
        newsletterForm.addEventListener('submit',submitNewsletter);
        newsletterForm.addEventListener('newsletter_fail',submitNewsletterFail);
    }

    function submitNewsletter(e){
        e.preventDefault();
        newsletterForm.classList.add('processing');
        newsletterButton.disabled = true;
        const formData = new FormData(e.target);
        const formArr = Array.from(formData.entries());
        let dataArr = {};
        formArr.forEach(item=>{
            if(item[0]!='tags'){
                dataArr[`${item[0]}`]=item[1];
            }
            else{
                dataArr['tags']=item[1].split(',');
            }
        })
        const data = JSON.stringify(dataArr);
        console.log(data);
        fetch(newsletterForm.getAttribute('action'), {
            method: 'post',
            headers: {
                'Accept': 'application/json, text/plain, */*',
                'Content-Type': 'application/json',
            },
            body: data
            })
            .then(response => response.json())
            .then(response => {
                if(response.response.code == 200){
                    let body = JSON.parse(response.body);
                    let id = body.id;
                    setCookie("SpCustomerID",id,6);
                    newsletterForm.classList.remove('processing');
                    newsletterForm.reset();
                    const event = new Event('newsletter_success');
                    newsletterForm.dispatchEvent(event);
                    newsletterButton.disabled = false;
                    window.location = newsletterForm.dataset.return;
                }
                else if(response.response.code == 400){
                    let body = JSON.parse(response.body);
                    console.log(body);
                    let message ='';
                    if(body.message == "Duplicate Entry"){
                        message = 'You have already signed up for our newsletter'
                    }
                    else if(body.message == "Bad Request"){
                        body.invalidInputs.forEach(input=>{
                            if(input.key=='Email'){
                                message += "Your email address is invalid.<br />";
                            }
                            if(input.key=='FirstName'){
                                message += "Sorry. Your first name is too long for our system.<br />";
                            }
                            if(input.key=='LastName'){
                                message += "Sorry. Your last name is too long for our system.<br />";
                            }
                        })
                    }
                    newsletterForm.classList.remove('processing');
                    const event = new CustomEvent('newsletter_fail',{detail:message});
                    newsletterForm.dispatchEvent(event);
                    newsletterButton.disabled = false;
                }
                else{
                    newsletterForm.classList.remove('processing');
                    const event = new CustomEvent('newsletter_fail',{detail:'Something went wrong. Please try again'});
                    newsletterForm.dispatchEvent(event);
                    newsletterButton.disabled = false;
                }
            })
            .catch(error=>{
                console.log(error);
                newsletterForm.classList.remove('processing');
                newsletterForm.reset();
                const event = new CustomEvent('newsletter_fail',{detail:'Something went wrong. Please try again'});
                newsletterForm.dispatchEvent(event);
                newsletterButton.disabled = false;
            })
    } 
    
	function submitNewsletterFail(e){
		newsletterMessage.innerHTML = e.detail;
		revealNewsletterMessage()
		//closeModalTarget(newsletterModal)
	}
	function revealNewsletterMessage(){
		newsletterMessage.classList.remove('hide');
		setTimeout(()=>{newsletterMessage.classList.add('hide');},4500);
	}

    function setCookie(name,value,hours) {
        var expires = "";
        if (hours) {
            var date = new Date();
            date.setTime(date.getTime() + (hours*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    }
})()