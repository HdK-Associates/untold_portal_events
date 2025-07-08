const apFormTags = document.querySelector('#ap_form_tags');
if(apFormTags){
    const formResponse = document.querySelector('#ap_form_tags_response');
    apFormTags.addEventListener('submit',(e)=>{
        e.preventDefault();
        const path = apFormTags.dataset.action;
        let formData = new FormData(apFormTags);
        apFormTags.classList.add('loader');
        fetch(path,{
            method:'POST',
            body:formData
        })
        .then(json=>json.json())
        .then(response=>{
            apFormTags.classList.remove('loader');
            formResponse.innerText = response;
            redirect = apFormTags.dataset.redirect;
            if(redirect&&formResponse!=='Something went wrong. Please try again later'){
                window.location.href = redirect;
            }
        })
        .catch(err=>{
            console.log(err);
            formResponse.innerText = err;
            apFormTags.classList.remove('loader');
        });
    })
}