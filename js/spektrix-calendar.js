(function(){
/*INSTANCES, SPEKTRIXBASEURL and SEATGAP are constant provided from wp_add_inline_script*/
const iframe = document.querySelector('#SpektrixIFrame');
const response = document.querySelector('#response');
const selectTickets = document.querySelector('#select_tickets');
const toBasket = selectTickets.classList.contains('api_component_to_basket');
const suppFrame = document.querySelector('#supp_frame');
const suppFrames = suppFrame?.querySelectorAll('.supp');
const suppEvent = typeof SUPP_EVENT !== "undefined";
const ticketTypes = typeof TICKETTYPES !== "undefined"?TICKETTYPES:false;
const clearButton = document.querySelector('#clear');
let currentSupp = false;
let controller;
let fetching = false; 
let flag = false;
let monthFlag = false;
let calendar = new Calendar({
    id:'#color-calendar',
    calendarSize:'small',
    eventsData:INSTANCES,
    dateChanged:(date,events)=>{
        if(flag){
            if(events.length>0){
                populateResponse(date,events);
            }
        }
        flag = true;
    },
    selectedDateClicked:(date,events)=>{
        if(flag){
            if(events.length>0){
                populateResponse(date,events);
            }
        }
        flag = true;
    },
    monthChanged: (currentDate,events)=>{
        if(monthFlag){
            flag=1;
        }
        monthFlag=true;
    }
});
window.addEventListener('load',setInitialDate);
document.querySelector('.spektrix-ticket')?.addEventListener('click',()=>{scrollToComponent('#buy_tickets_api_component');});

function setInitialDate(){
    calendar.setDate(INSTANCES[0].start);
    calendar.calendar.classList.add('loaded');
}

function scrollToComponent(component){
    setTimeout(()=>{
   // let scrollTarget = document.querySelector('#buy_tickets_api_component').getBoundingClientRect().top - 185;
    let scrollTarget = window.scrollY + document.querySelector(component).getBoundingClientRect().top - 145;
    window.scrollTo({top:scrollTarget,behaviour:'smooth'});
    },500);
}

async function populateResponse(date,events){
   // let eventDisabledCache = [];
    response.classList.add('slow-fade');
    response.classList.add('loading');
    let dateJFY = processDateJFY(date);
    let str = `<h3>${dateJFY}</h3>`;
    
    
    if(events){
        str+=`<h4>Select a time</h4><ul class="spektrix_api_times"></ul><h4 class="no_tickets d-none">No tickets available for this time. Please select again</h4>`;
        response.innerHTML = str;
        let times = response.querySelector('.spektrix_api_times');
        response.classList.remove('slow-fade');
        for(const event of events){
        //await events.forEach(async event=>{
            let eventDisabled = event.available?'':'disabled';
            let _ticketTypes = event?.ticketTypes;
            if(!_ticketTypes){
                _ticketTypes = ticketTypes;
            }
            if(SEATGAP){
                let status = await fetchStatus(event.id).then(response=>{return response});
                if(status.available<SEATGAP){
                    eventDisabled = 'disabled';
                    //eventDisabledCache.push(event.id);
                }  
            }
            let data =''
            if(toBasket || suppEvent ){
                data = `data-seatingplan=${event.seatingplan} data-type=${event.type}`;
            }
            if(suppEvent){
                data+=` data-eventid=${event.eventid}`;
            } 
            if(_ticketTypes){
                data = `data-tickettypes="true" data-timestr="${dateJFY}: ${event.formattedTime}"`;
            }
            times.insertAdjacentHTML('beforeend',`<li class="spektrix_api_time "><input type="radio" id="event_${event.start}" value="${event.id}" data-start="${event.start}" data-shortid="${event.shortid}" name="time_select" ${eventDisabled} ${data}><label for="event_${event.start}">${event.formattedTime}</label></li>`);
        //})
        }
        //str += `</ul>`;
    }
    else{
        str+='<h4>No times available on this date</h4>';
        response.innerHTML = str;
        response.classList.remove('slow-fade');
    }
    response.classList.remove('loading');
    /* if(eventDisabledCache.length){
        let cacheDate = dateJFY.replace(' ','_');
        let body = {
            'date':cacheDate,
            'events':eventDisabledCache,
            'id':EVENTID,
        }
        fetch('/wp-json/ap/v1/cache_disabled_date',{
            "method":"POST",
            "headers": {
                'Content-Type': 'application/json',
              },
            "body":JSON.stringify(body)
        });
    } */
    //setTimeout(()=>response.classList.remove('slow-fade'),400);

}
function populateResponseTicketTypes(event){
    let _ticketTypes;
    _ticketTypes = INSTANCES.find((instance)=>{
        return instance.id == event.value;
    })?.ticketTypes;
    if(!_ticketTypes){
        _ticketTypes = ticketTypes;
    }
    response.classList.add('slow-fade');
    response.classList.add('loading');
    let str = `<div id="supp_ticket_frame"><h3>${event.dataset.timestr}</h3>`;
    str+=`<h4>Select a ticket</h4><form class="spektrix_api_types" id="spektrix_api_types" data-start="${event.dataset.start}" data-shortid="${event.dataset.shortid}" data-id="${event.value}" ></form><h4 class="no_tickets d-none">No tickets available for this time. Please select again</h4></div>`;
    response.innerHTML = str;
    let typesArea = response.querySelector('.spektrix_api_types');
    response.classList.remove('slow-fade');
    for(const type in _ticketTypes){
        typesArea.insertAdjacentHTML('beforeend',`<div class="TicketType spektrix_api_type">
        <label for="event_${_ticketTypes[type]['type']}">${type}</label>
        <span class="price">£${_ticketTypes[type]['price']}</span>
        <input type="number" id="event_${_ticketTypes[type]['type']}" data-type="${_ticketTypes[type]['type']}" data-plan="${_ticketTypes[type]['seatingPlan']}" min="0"></li>`);
    }
    const submitButton = document.createElement('button');
    submitButton.classList.add('btn', 'ap_btn_fill_icon', 'ap_btn_pink', 'btn_subscribe');
    submitButton.id = 'submit_types';
    submitButton.innerHTML = 'Next';
    submitButton.addEventListener('click',ticketTypesHandler);
    typesArea.insertAdjacentElement('beforeend',submitButton);
}

response.addEventListener('change',(e)=>{
    if(e.target.tagName!=="INPUT"||e.target.type=='number'){
        return;
    }
    if(e.target.dataset.tickettypes){
        populateResponseTicketTypes(e.target);
    }
    else{
        console.log(e.target);
        checkAvailable(e.target.value,e.target.dataset.shortid,null,null);
    }
})

function ticketTypesHandler(e){
    e.preventDefault();
    const data = [];
    const id = e.target.parentElement.dataset.id;
    const inputs = e.target.parentElement.querySelectorAll('input');
    let count = 0;
    const instance={
        'id':id,
        'shortid':e.target.parentElement.dataset.shortid,
        'start':e.target.parentElement.dataset.start,
    }
    inputs.forEach(input=>{
        if(input.value){
            count += parseInt(input.value);
            for(let i=0;i<input.value;i++){
                data.push({
                    'instance':id,
                    'seatingPlan':input.dataset.plan,
                    'type':input.dataset.type
                });
            }
        }
    });
    checkAvailable(id,e.target.parentElement.dataset.shortid,data,count,instance);
}

async function checkAvailable(id,shortID,typeData,quantity,instance){
    response.classList.add('loader');
    if(fetching){
        controller.abort();
        fetching=false;
    }
    controller = new AbortController();
    const signal = controller.signal;
    fetching = true; 
    data = await fetchStatus(id);
    if((SEATGAP && data.available>=SEATGAP) || (SEATGAP==false && data.available>0) || (quantity && quantity<=data.available)){
        if(suppEvent || toBasket){
            addToBasket(typeData, instance);
        }
        else{
            iframe.src=iframe.dataset.src.replace('__id__',shortID);
            iframe.parentElement.classList.remove('d-none');
            selectTickets.classList.add('d-none');
        }
    }
    else{
        response.querySelector('.no_tickets').classList.remove('d-none');
    }
    response.classList.remove('loader');
    fetching = false;
}

async function fetchStatus(id){
    let data = await fetch(`${SPEKTRIXBASEURL}/${id}/status?includeChildPlans=true`);
    let dataJSON = await data.json();
    return dataJSON;
}

function addToBasket(data, instance){
    postData(SPEKTRIXBASKETURL,data)
    .then(data=>{
        if(data.errorCode){
            console.log(data.message);
        }
        else{
            if(suppEvent){
                renderNextFrame(instance);
                suppFrame.classList.remove('loader');
                //document.querySelector('.event_buttons').classList.add('d-none');
            }
            else{
                iframe.src=iframe.dataset.src.replace('__id__',instance.dataset.shortid);
                iframe.parentElement.classList.remove('d-none');
                selectTickets.classList.add('d-none');
            }
        }
    }).catch(error=>console.log(error));
}

function processDateJFY(date){
    var months = {
        'Jan' : 'January',
        'Feb' : 'February',
        'Mar' : 'March',
        'Apr' : 'April',
        'May' : 'May',
        'Jun' : 'June',
        'Jul' : 'July',
        'Aug' : 'August',
        'Sep' : 'September',
        'Oct' : 'October',
        'Nov' : 'November',
        'Dec' : 'December'
    }
    date = date +'';
    date = date.slice(4,15);
    today =new Date();
    today = today +'';
    today = today.slice(4,15);
    if(date == today){
        return 'Today';
    }
    else{
        date = date.split(' ');
        if(date[1].charAt(0)=='0'){
            date[1] = date[1].slice(1,2);
        }
        return `${date[1]} ${months[date[0]]} ${date[2]}`;
    }
}



async function renderNextFrame(previousInstance){
    scrollToComponent('#supp_frame');
    selectTickets.classList.add('d-none');
    suppFrame.classList.add('active');
    clearButton.classList.remove('d-none');
    if(currentSupp===false){
        currentSupp = 0;
    }
    else{
        currentSupp++;
    }
    if(!SUPP_INSTANCES[currentSupp]){
        //addOrderAttributesToBasket();
        window.location = "/basket";
        return;
    }
    let id = SUPP_INSTANCES[currentSupp]['post_id'];
    let thisSupp;
    suppFrames.forEach(supp=>{
        if(supp.id == `supp_${id}`){
            supp.classList.remove('d-none');
            thisSupp = supp;
        }
        else{
            supp.classList.add('d-none');
        }
    });
    let addedDate;
    if(previousInstance.start){
        addedDate = previousInstance.start;  
    }
    else{
        addedDate = previousInstance.id.split('_')[1];
    }
    let instance = SUPP_INSTANCES[currentSupp]['instances'].find((value)=>{
        return value.start==addedDate;
    })
    if(!instance){
        instance = SUPP_INSTANCES[currentSupp]['instances'][0];
    }
    let instanceData = await fetchStatus(instance.id);
    totalAvailable = instanceData.available;
    let tickets = Object.keys(instance.ticketTypes);
    let form = document.createElement('form');
    let date = new Date(instance.start);
    let dateStr = processDateJFY(date);
    let dateNode = document.createElement('h3');
    dateNode.innerHTML = dateStr;
    form.appendChild(dateNode);
    let allInputs = [];
    tickets.forEach(ticket=>{
        let label = document.createElement('label');
        
        label.innerHTML = `${ticket}` ;
        label.setAttribute('for', `id_${instance['ticketTypes'][ticket]['type']}`);

        let span = document.createElement('span');
        let amount = instance['ticketTypes'][ticket]['price']?'£'+padDecimal(instance['ticketTypes'][ticket]['price']):'Free';
        span.innerHTML = amount;
        let input = document.createElement('input');
        input.id = `id_${instance['ticketTypes'][ticket]['type']}`;
        input.setAttribute('name',`id_${instance['ticketTypes'][ticket]['type']}`);
        input.setAttribute('type','number');
        input.setAttribute('max',totalAvailable);
        input.setAttribute('min',0);
        input.seatingPlan = instance['ticketTypes'][ticket]['seatingPlan'];
        let div = document.createElement('div');
        div.classList.add('TicketType');
        div.appendChild(label);
        div.appendChild(span);
        div.appendChild(input);
        form.appendChild(div);
        allInputs.push(input);
    });
    let submit = document.createElement('button');
    submit.setAttribute('type','submit');
    submit.classList.add('btn', 'ap_btn_fill_icon', 'ap_btn_pink', 'btn_subscribe');
    submit.innerHTML = "Next";
    
    form.appendChild(submit);

    let errorSpace = document.createElement('p');
    form.appendChild(errorSpace);

    thisSupp.appendChild(form);
    form.addEventListener('submit',(e)=>{
        e.preventDefault();
        let count=0;
        let data=[];
        suppFrame.classList.add('loader');
        allInputs.forEach((input)=>{
                if(input.value){
                    count += parseInt(input.value);
                    let type = input.id.split('_')[1];
                    for(let i=0;i<input.value;i++){
                        data.push({
                            "instance":instance.id,
                            "seatingPlan":input.seatingPlan,
                            "type":type
                        })
                    }
                }
            }
        )
        if(count<=totalAvailable){
            addToBasket(data,instance);
        }
        else{
            suppFrame.classList.remove('loader');
            errorSpace.innerHTML=`You cannot add more than ${totalAvailable} to your basket`
        }
    })
}

async function addOrderAttributesToBasket(){
    suppFrames.forEach(supp=>{
        supp.classList.add('d-none');
    });
    let input = document.createElement('input');
    input.setAttribute('type','text');
    input.setAttribute('data-attr',`Attribute_let us know of any food allergies or intolerances`);
    input.setAttribute('maxlength','255')
    let label = document.createElement('label');
    label.innerHTML = 'Let us know of any food allergies or intolerances';
    let submit = document.createElement('button');
    submit.setAttribute('type','submit');
    submit.classList.add('btn', 'ap_btn_fill_icon', 'ap_btn_pink', 'btn_subscribe');
    submit.innerHTML = "Next";
    let form = document.createElement('form');
    form.appendChild(label);
    form.appendChild(input);
    form.appendChild(submit);
    let frame = document.querySelector('#attributes');
    frame.classList.remove('d-none');
    frame.appendChild(form);
    form.addEventListener('submit',(e)=>{
        e.preventDefault();
        suppFrame.classList.add('loader');
        let data = [
            {
                'Attribute_let us know of any food allergies or intolerances':input.value
            }
        ]
        patchData(SPEKTRIXBASKETURL,data)
        .then((data)=>{
            window.location = "/basket";
        });
    });
}

async function postData(url = '', data = {}) {
    console.log(data);
    console.log(url);
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      credentials: 'include',
      body: JSON.stringify(data) 
    });
    return response.json(); 
}

async function patchData(url = '', data = {}) {
    const response = await fetch(url, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
      },
      credentials: 'include',
      body: JSON.stringify(data) 
    });
    return response.json(); 
}

clearButton?.addEventListener('click',(e)=>{
    e.target.classList.add('d-none');
    suppFrames.forEach(supp=>{
        supp.classList.add('d-none');
        supp.innerHTML='';
    });
    selectTickets.classList.remove('d-none');
    suppFrame.classList.remove('active');
    postData(SPEKTRIXCLEARBASKETURL,{});
})

function padDecimal(str){
    let splitStr = str.toString().split('.');
    if(splitStr[1]){
        splitStr[1] = splitStr[1].padEnd(2,0);
    }
    return splitStr.join('.');
}
})();