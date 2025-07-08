(function(){
    const submitButton = document.querySelector('#refresh_now');
    let initialSubmit = false;
    if(submitButton){
        initialSubmit = submitButton.dataset?.init;
    }
    const submitSingleEventButton = document.querySelector("#refresh_single_event");
    const importEventButton = document.querySelector("#import_single_event");
    const trackContainer = document.querySelector('#track-container');
    const trackInner = document.querySelector('#track-inner');
    const submitButtons = document.querySelectorAll('input[type=submit]');
    const currentPlace = sessionStorage.getItem('spektrixEventCurrent');
    const eventInput = document.getElementById('single_event');
    const pageSelect = document.getElementById('page_select');
    const webComponents = document.getElementById('hdk-webcomponents-boxes');
    const componentForm = document.getElementById('components');
    const modalButtons = document.querySelectorAll('#event_modal button');

    if(eventInput){
        var eventInputList=[];
        eventsList.forEach(event=>{
            let eventObj={label:event.event_name,value:event.event_id};
            eventInputList.push(eventObj);
        })

        new Awesomplete(eventInput,{
            list:eventInputList,
            replace: function(suggestion) {
                this.input.value = suggestion.label;
                this.input.dataset.event_id = suggestion.value;
            }
        })
    }

    if(pageSelect){
        let pageInputList=[];
        const buttonContainer = document.querySelector('#button_container');
        const actualInput = document.querySelector('#hdk-iframe-pages');
        buttonContainer.addEventListener('click',e=>{
            e.target.remove();
            getActualInput(buttonContainer,actualInput);
        });
        pageList.forEach(page=>{
            let pageObj={label:page.page_name,value:page.page_id};
            pageInputList.push(pageObj);
        });

        new Awesomplete(pageSelect,{
            list:pageInputList,
            replace: function(suggestion) {
                this.input.value = suggestion.label;
                this.input.dataset.page_id = suggestion.value;
            }
        });
        document.addEventListener('awesomplete-selectcomplete',(e)=>{
            if(e.target.id=='page_select'){
                let name = e.target.value;
                let id = e.target.dataset.page_id;
                let buttons = buttonContainer.querySelectorAll('button');
                buttons.forEach(button=>{
                    if(button.dataset.page_id == id){
                        e.target.value = '';
                        e.target.dataset.page_id = '';
                        return;
                    }
                });
                buttonContainer.insertAdjacentHTML('beforeend',`<button data-page_id=${id}>${name}</button>`);
                e.target.value = '';
                e.target.dataset.page_id = '';
                getActualInput(buttonContainer,actualInput);
            }
        });
    }

    function getActualInput(buttonContainer, actualInput){
        let buttons = buttonContainer.querySelectorAll('button');
        let data = [];
        buttons.forEach(button=>{
            data.push(button.dataset.page_id);
        });
        actualInput.value = JSON.stringify(data);
    }
    /*prevent form from submitting if enter on page selector*/
    /*componentForm.addEventListener('submit',e=>e.preventDefault());
    componentForm.addEventListener('keydown',e=>{
        console.log(e);
        return e.key != "Enter";
    });*/

    if(webComponents){
        const actualInputComponents = document.querySelector('#hdk-webcomponents');
        const checkBoxes = webComponents.querySelectorAll('input[type=checkbox]');
        webComponents.addEventListener('click', e =>{
            let data = [];
            checkBoxes.forEach(checkBox=>{
                if(checkBox.checked){
                    data.push(checkBox.value);
                }
            })
            actualInputComponents.value = JSON.stringify(data);
        })
    }
    if(currentPlace && currentPlace!=0){
        updateInstance(currentPlace);
    }
    if(submitButton){
        submitButton.addEventListener('click',updateEvents);
    }

    if(submitSingleEventButton){
        submitSingleEventButton.addEventListener('click',updateSingleEvent);
    }

    if(importEventButton){
        importEventButton.addEventListener('click',importEvent);
    }
    function updateEvents(e){
        e.preventDefault();
        submitButton.disabled = true;
        if(submitSingleEventButton){
            submitSingleEventButton.disabled = true;
        }
        if(importEventButton ){
            importEventButton.disabled = true;
        }

        submitButtons.forEach(button=>button.disabled=true);
        trackContainer.classList.add('show');
        fetch(`/wp-json/spektrix/v1/update/`,{
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': spektrixnonce.nonce,
            }
        })
        .then(response=>response.json())
        .then(data => {
            console.log(data);
            if(data=='error'){
                console.log('error');
                fetch(`/wp-json/spektrix/v1/error`,{
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': spektrixnonce.nonce,
                    }
                })
                .then(response=>response.json())
                .then(data => {
                    console.log(data);
                    sessionStorage.removeItem('spektrixEventTotal');
                    sessionStorage.removeItem('spektrixEventCurrent');
                    sessionStorage.removeItem('spektrixRecordsCount');
                });
            }
            else{
                sessionStorage.setItem('spektrixEventTotal', parseInt(data));
                sessionStorage.setItem('spektrixEventCurrent', 1);
                sessionStorage.setItem('spektrixRecordsCount', parseInt(data));
                updateInstance(1,0);
            }
        })
        .catch(error=>{
            console.log(error);
        })
    }
    function updateSingleEvent(e){
        e.preventDefault();
       if(eventId=eventInput.dataset.event_id){
            submitButton.disabled = true;
            submitSingleEventButton.disabled = true;
            if(importEventButton ){
                importEventButton.disabled = true;
            }
            submitButtons.forEach(button=>button.disabled=true);
            trackContainer.classList.add('show');
            fetch(`/wp-json/spektrix/v1/updatesingleevent/${eventId}`,{
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': spektrixnonce.nonce,
                }
            })
            .then(response=>response.json())
            .then(data => {
                sessionStorage.setItem('spektrixEventTotal', parseInt(data[1]));
                sessionStorage.setItem('spektrixEventCurrent', data[1]);
                sessionStorage.setItem('spektrixRecordsCount', parseInt(data[0]));
                updateInstance(data[1],0,true);
            })
            .catch(error=>{
                console.log(error);
                fetch(`/wp-json/spektrix/v1/error`,{
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': spektrixnonce.nonce,
                    }
                })
                .then(response=>response.json())
                .then(data => {
                    sessionStorage.removeItem('spektrixEventTotal');
                    sessionStorage.removeItem('spektrixEventCurrent');
                    sessionStorage.removeItem('spektrixRecordsCount');
                });
            })
        }
    }

    function updateInstance(count, chunk, single=false){
        submitButton.disabled = true;
        if(submitSingleEventButton){
            submitSingleEventButton.disabled = true;
        }
        submitButtons.forEach(button=>button.disabled=true);
        trackContainer.classList.add('show');
        let total = sessionStorage.getItem('spektrixEventTotal'); 
        console.log(count);
        trackInner.style.maxWidth = `${count / total * 300}px`;
        if(count<=total){
            fetch(`/wp-json/spektrix/v1/updateinstances?count=${count}&chunk=${chunk}&single=${single}`,{
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': spektrixnonce.nonce,
                }
            })
            .then(response=>response.json())
            .then(data => {
                if('Error'!=data){
                    let count = parseInt(data[0]);
                    let chunk = parseInt(data[2]);
                    if(!data[3]){
                        count = count + 1;
                        chunk = 0;
                    }
                    let records = sessionStorage.getItem('spektrixRecordsCount');
                    sessionStorage.setItem('spektrixEventCurrent', count );
                    if(data[1]){
                        sessionStorage.setItem('spektrixRecordsCount',parseInt(records) + parseInt(data[1]));
                    }
                    updateInstance(count, chunk, single);
                } 
                else{
                    console.log('error');
                }
            })
            .catch(error=>{
                console.log(error);
                fetch(`/wp-json/spektrix/v1/error`,{
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': spektrixnonce.nonce,
                    }
                })
                .then(response=>response.json())
                .then(data => {
                    console.log(data);
                    sessionStorage.removeItem('spektrixEventTotal');
                    sessionStorage.removeItem('spektrixEventCurrent');
                    sessionStorage.removeItem('spektrixRecordsCount');
                });
            })
        }
        else{
            console.log('done');
            sessionStorage.removeItem('spektrixEventCurrent');
            
            trackContainer.classList.remove('show');
            let recordsTotal = sessionStorage.getItem('spektrixRecordsCount');
            document.querySelector('#event_message').innerHTML = `${recordsTotal} records inserted`;
            if(initialSubmit){
                fetch(`/wp-json/spektrix/v1/initialpopulate`,{
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': spektrixnonce.nonce,
                    }
                })
                .then(response=>response.json())
                .then(data => {
                    console.log(data);
                    fetch(`/wp-json/spektrix/v1/populatecomplete`,{
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': spektrixnonce.nonce,
                        }
                    })
                    .then(response=>response.json())
                    .then(data => {
                        console.log(data);
                        fetch(`/wp-json/spektrix/v1/insertpopulatecomplete`,{
                            headers: {
                                'Content-Type': 'application/json',
                                'X-WP-Nonce': spektrixnonce.nonce,
                            }
                        })
                        .then(response=>response.json())
                        .then(data => {
                            document.querySelector('#event_message').innerHTML='<strong>Complete</strong>';
                            console.log(data);
                            submitButton.disabled = false;
                        if(submitSingleEventButton){
                            submitSingleEventButton.disabled = false;
                        }
                        if(importEventButton ){
                            importEventButton.disabled = false;
                        }
                        submitButtons.forEach(button=>button.disabled=false);
                        });
                        
                    })
                })
            }
            else if(!single){
                fetch(`/wp-json/spektrix/v1/populatecomplete`,{
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': spektrixnonce.nonce,
                    }
                })
                .then(response=>response.json())
                .then(data => {
                    console.log(data);
                    data = JSON.parse(data);
                    document.querySelector('#event_modal_content').innerHTML=`<h3>Please review carefully before accepting</h3><p>The following new events are being added to the database: <ul><li>${data.newRelations.join('</li><li>')}</li></ul></p><p>The following events and instances are being added to the database: <ul><li>${data.newEvents.join('</li><li>')}</li></ul>`;
                    document.querySelector('#event_modal').style.display='block';
                    modalButtons.forEach(button=>button.disabled=false);
                })
            }
            else{
                fetch(`/wp-json/spektrix/v1/singlecomplete`,{
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': spektrixnonce.nonce,
                    }
                })
                .then(response=>response.json())
                .then(data => {
                    console.log(data);
                    submitButton.disabled = false;
                    if(submitSingleEventButton){
                        submitSingleEventButton.disabled = false;
                    }
                    if(importEventButton ){
                        importEventButton.disabled = false;
                    }
                    submitButtons.forEach(button=>button.disabled=false);
                }) 
            }
        }
    }

    function importEvent(e){
        e.preventDefault();
        const spektrixIDInput = document.querySelector('#import_event');
        const eventId = spektrixIDInput.value;
        if(eventId){
            submitButton.disabled = true;
            submitSingleEventButton.disabled = true;
            importEventButton.disabled = true;
            submitButtons.forEach(button=>button.disabled=true);
            trackContainer.classList.add('show');
            fetch(`/wp-json/spektrix/v1/importevent/${eventId}`,{
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': spektrixnonce.nonce,
                }
            })
            .then(response=>response.json())
            .then(data => {
                if(data.hasOwnProperty('error')){
                    document.querySelector('#event_message').innerHTML=data.error;
                }
                else{
                    document.querySelector('#event_message').innerHTML='Success'
                }
                trackContainer.classList.remove('show');
                submitButton.disabled = false;
                if(submitSingleEventButton){
                    submitSingleEventButton.disabled = false;
                }
                importEventButton.disabled = false;
                ;
            })
            .catch(error=>{
                document.querySelector('#event_message').innerHTML=error.error;
                trackContainer.classList.remove('show');
                console.log(error);
            });
        }
    }
            


    modalButtons.forEach(button=>button.addEventListener('click',(e)=>{
        if(e.currentTarget.id == 'event_modal_accept'){
            fetch(`/wp-json/spektrix/v1/insertpopulatecomplete`,{
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': spektrixnonce.nonce,
                }
            })
            .then(response=>response.json())
            .then(data => {
                document.querySelector('#event_message').innerHTML='<strong>Complete</strong>';
                console.log(data);
                closeModal();
            });
        }
        else if(e.currentTarget.id == 'event_modal_reject'){
            fetch(`/wp-json/spektrix/v1/rejectpopulatecomplete`,{
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': spektrixnonce.nonce,
                }
            })
            .then(response=>response.json())
            .then(data => {
                document.querySelector('#event_message').innerHTML='<strong>Please try again</strong>';
                console.log(data);
                closeModal();
            });
            closeModal();
        }
    }))

    function closeModal(){
        document.querySelector('#event_modal').style.display='none';
        submitButton.disabled = false;
        if(submitSingleEventButton){
            submitSingleEventButton.disabled = false;
        }
        submitButtons.forEach(button=>button.disabled=false);
        modalButtons.forEach(button=>button.disabled=true);
    }
    /*Base Settings form*/
    let merchCheckbox = document.querySelector('#hdk-merch');
    if(merchCheckbox){
        let eventSelect = document.querySelector('#hdk-event-cpt');
        let merchSelect = document.querySelector('#hdk-merch-cpt');
        let eventSelectOptions = eventSelect.querySelectorAll('option');
        let merchSelectOptions = merchSelect.querySelectorAll('option');
        let merchSelectContainer = document.querySelector('#merch-select');
        merchCheckbox.addEventListener('change',()=>{
            if(merchCheckbox.checked){
                merchSelectContainer.style.display = "table-row";
            }
            else{
                merchSelectContainer.style.display = "none";
                merchSelect.value = 'default';
            }
        });
        eventSelect.addEventListener('change',()=>{
            disableOption(eventSelect,merchSelectOptions);
        });
        merchSelect.addEventListener('change',()=>{
            disableOption(merchSelect,eventSelectOptions);
        });
    }

    function disableOption(optionValue, optionArray){
        if(optionValue.value!='default'){
            optionArray.forEach((option)=>{
                if(option.value == optionValue.value){
                    option.disabled = true;
                }
            });
        }
        else{
            optionArray.forEach((option)=>{
                option.disabled = false;
            });
        }
    }

}());