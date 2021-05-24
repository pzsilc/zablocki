class Messages{
    constructor(){
        [...document.querySelectorAll('.unread-message')].forEach(i => {
            i.addEventListener('click', this.click)
        });
    }

    click(e){
        const { target, id } = e.target.dataset;
        $.get('/zablocki/orders/messages/delete?id=' + id, {}, data => { console.log(data); });
        window.location.replace(target);
    }
}

if([...document.querySelectorAll('.unread-message')].length > 0){
    window.onload = () => {
        let messages = new Messages();
    }
}