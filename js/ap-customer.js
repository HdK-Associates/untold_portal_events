let loginCheckDiv = document.querySelector('#login_check');
let iframeShowing = false;
async function fetchCustomer(){
    let data = await fetch(`${SPEKTRIXBASEURLCUSTOMER}?$expand=tags`,{credentials: 'include',});
    if (!data.ok) {
        console.log(`Either you are not logged in or I can't fetch your details: ${data.status}`);
      }
    let dataJSON = await data.json();

    return dataJSON;
}

function checkTag(customer){
    customer.tags.forEach(tag=>{
        if(SPEKTRIXTAGNAME.includes(tag.name)||SPEKTRIXTAGNAME.includes(tag.id)){ 
            showIframe();
            iframeShowing =true;
        }
        
    });
    if(!iframeShowing){
        let message = loginCheckDiv.querySelector('.message');
        message.innerHTML = SPEKTRIXNOACCESSMESSAGE;
    }

}

function showIframe(){
    loginCheckDiv.classList.add('show_iframe');
}

function checkLoggedIn(){
fetchCustomer()
    .then(
        customer => { 
            console.log(customer);
            if(!customer.hasOwnProperty('id')) return;
            if(SPEKTRIXTAGNAME&&SPEKTRIXTAGNAME.length>0){
                checkTag(customer);
                return;
            }
            showIframe();
            iframeShowing = true;
        }
    )
}

checkLoggedIn();

document.addEventListener('visibilitychange',()=>{
    if(document.visibilityState=='visible'){
        checkLoggedIn();
    }
})
