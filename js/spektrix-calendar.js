(function(){
/*INSTANCES, SPEKTRIXBASEURL and SEATGAP are constant provided from wp_add_inline_script*/
const iframe = document.querySelector('#SpektrixIFrame');
const response = document.querySelector('#time_response');
const ticket_response = document.querySelector('#ticket_response');
const selectTickets = document.querySelector('#select_tickets');
const toBasket = selectTickets.classList.contains('api_component_to_basket');
const suppFrame = document.querySelector('#supp_frame');
const suppFrames = suppFrame?.querySelectorAll('.supp');
const suppEvent = typeof SUPP_EVENT !== "undefined";
const ticketTypes = typeof TICKETTYPES !== "undefined"?TICKETTYPES:false;
const clearButton = document.querySelector('#clear');
const admissionSection = document.querySelector('.admission_section');
let page = CURRENT_PAGE;
if(page!==1){
    renderNextFrame(false);
}
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
    let emit = new CustomEvent('dateChanged',{bubbles:true,detail:{date:date}});
    response.dispatchEvent(emit);

    
    if(events){
        str+=`<h4>Select a time</h4><ul class="spektrix_api_times"></ul><h4 class="no_tickets hide">No tickets available for this time. Please select again</h4>`;
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
    ticket_response.classList.add('slow-fade');
    ticket_response.classList.add('loading');
    let str = `<div id="supp_ticket_frame">`;
    str+=`<h4>Who's coming?</h4><p>Tell us how many people will be visiting</p><form class="spektrix_api_types" id="spektrix_api_types" data-start="${event.dataset.start}" data-shortid="${event.dataset.shortid}" data-id="${event.value}" ></form><h4 class="no_tickets hide">No tickets available for this time. Please select again</h4></div>`;
    ticket_response.innerHTML = str;
    let typesArea = ticket_response.querySelector('.spektrix_api_types');
    ticket_response.classList.remove('slow-fade');
    for(const type in _ticketTypes){
        let div = document.createElement('div');
        div.classList.add('TicketType','spektrix_api_type');
        let label = document.createElement('label');
        label.setAttribute('for',`event_${_ticketTypes[type]['type']}`);
        label.innerHTML = type;
        div.appendChild(label);
        let divInput = document.createElement('div');
        divInput.classList.add('input_wrap');
        div.appendChild(divInput);
        let buttonDecrease = document.createElement('button');
        buttonDecrease.classList.add('decrease');
        buttonDecrease.classList.add('increment_decrement');
        buttonDecrease.setAttribute('type','button');
        buttonDecrease.innerHTML = '<span>&mdash;</span>';
        let buttonIncrease = document.createElement('button');
        buttonIncrease.classList.add('increase');
        buttonIncrease.classList.add('increment_decrement');
        buttonIncrease.setAttribute('type','button');
        buttonIncrease.innerHTML = '<span>+</span>';
        divInput.appendChild(buttonDecrease);
        let input = document.createElement('input');
        input.setAttribute('type','number');
        input.dataset.type = _ticketTypes[type]['type'];
        input.dataset.plan = _ticketTypes[type]['seatingPlan'];
        input.value = '0';
        input.dataset.value = '0';
        input.setAttribute('id',`event_${_ticketTypes[type]['type']}`);
        input.setAttribute('min','0');
        let span = document.createElement('span');
        span.classList.add('price');
        span.innerHTML = `£${_ticketTypes[type]['price']}`;
        divInput.appendChild(input);
        divInput.appendChild(buttonIncrease);
        div.appendChild(span);
        typesArea.appendChild(div);
        buttonDecrease.addEventListener('click',(e)=>{
            e.preventDefault();
            input.value = Math.max(0, (parseInt(input.value) || 0) - 1);
            input.dataset.value = input.value;
        });
        buttonIncrease.addEventListener('click',(e)=>{
            e.preventDefault();
            input.value = (parseInt(input.value) || 0) + 1;
            input.dataset.value = input.value;
        });
    }
    const p = document.createElement('p');
    p.innerHTML = "For group bookings over 15 and school bookings please <a href='/contact'>contact us</a>";
    typesArea.insertAdjacentElement('beforeend',p);
    const submitButton = document.createElement('button');
    submitButton.classList.add('button', 'bg_light_green');
    submitButton.id = 'submit_types';
    submitButton.innerHTML = 'Continue';
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
    if(ticket_response.querySelector('.error')){
        ticket_response.querySelector('.error').remove();
    }
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
    if(!count){
        let error = document.createElement('p');
        error.classList.add('error');
        error.innerHTML = "Please select at least one ticket.";
        ticket_response.appendChild(error);
    }
    else{
        checkAvailable(id,e.target.parentElement.dataset.shortid,data,count,instance);
    }
}

async function checkAvailable(id,shortID,typeData,quantity,instance){
    ticket_response.classList.add('loader');
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
        response.querySelector('.no_tickets').classList.remove('hide');
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
    console.log(data);
    postData(SPEKTRIXBASKETURL,data)
    .then(data=>{
        if(data.errorCode){
            console.log(data.message);
        }
        else{
            renderNextFrame();
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



async function renderNextFrame(advance=true){
    admissionSection.classList.remove(`show_step_${page}`);
    if(advance){
        page++;
    }
    admissionSection.classList.add(`show_step_${page}`);
    window.history.pushState("","",`?step=${page}`);
    if(page==2){
        let basketIframe = document.querySelector('.basket_iframe iframe');
        basketIframe.src = basketIframe.dataset.src;
    }
    if(page==3){
        let checkoutIframe = document.querySelector('.checkout_iframe iframe');
        checkoutIframe.src = checkoutIframe.dataset.src;
    }
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