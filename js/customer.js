(function(){
    async function fetchCustomer(){
        let data = await fetch(`${SPEKTRIXBASEURLCUSTOMER}?$expand=subscriptions`,{credentials: 'include',});
        if (!data.ok) {
            console.log(`Either you are not logged in or I can't fetch your details: ${data.status}`);
        }
        let dataJSON = await data.json();

        return dataJSON;
    }

    async function checkLoggedIn(customer=false){
        if(!customer){
            customer = await fetchCustomer();
        }
        if(customer.hasOwnProperty('id')){
            document.cookie = `spektrix_customer=${JSON.stringify(customer)}; path=/;`;
        }
        else{
            customer = null;
            document.cookie = `spektrix_customer=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT`;
        }
        
        let log_in_area = document.querySelectorAll('.login-check');
        if(log_in_area.length>0){
            log_in_area.forEach(area=>{
                if(!customer){
                    area.classList.remove('logged_in','has_membership','no_membership');
                    area.classList.add('logged_out');
                }
                else{
                    let membershipCheck = area.dataset.memberships;
                    if(membershipCheck){
                        let memberships = membershipCheck.split(',');
                        let subscriptions = customer.subscriptions?.map(sub => sub.membership.id) || [];
                        let hasMembership = subscriptions.filter(sub => memberships.includes(sub)).length > 0;
                        if(hasMembership){
                            area.classList.remove('logged_out','no_membership');
                            area.classList.add('logged_in','has_membership');
                            iframes = area.querySelectorAll('iframe');
                            iframes.forEach(iframe=>{
                                let src = iframe.dataset.src;
                                if(src && iframe.src!=src){
                                    iframe.src = src;
                                }
                            });
                        } else {
                            area.classList.remove('logged_out','has_membership');
                            area.classList.add('logged_in','no_membership');
                        }
                    } else {
                        area.classList.remove('logged_out','has_membership','no_membership');
                        area.classList.add('logged_in');
                    }
                }
            });
        }
    }

    checkLoggedIn();

    document.addEventListener('visibilitychange',()=>{
        if(document.visibilityState=='visible'){
            checkLoggedIn();
        }
    })

    document.addEventListener('spektrixLoginSuccess',(e)=>{
        checkLoggedIn(e.detail);
    });
}());