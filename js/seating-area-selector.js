(function(){
    const selectors = document.querySelectorAll('#seating_area_selector button');
    if(!selectors.length){
        return;
    }   
    const iframe = document.querySelector('#SpektrixIFrame');
    const iframeWrapper = document.querySelector('.iframe-wrapper');
    const selectorWrapper = document.querySelector('.seating-area-selector-wrapper');
    selectors.forEach((selector)=>{
        selector.addEventListener('click', handleSelection);
    });

    function handleSelection(e){
        const button = e.currentTarget;
        const id = button.getAttribute('data-id');
        iframe.src=iframe.dataset.src.replace('__seatingPlanId__',id);
        iframeWrapper.classList.remove('d-none');
        selectorWrapper.classList.add('d-none');
    }
})();