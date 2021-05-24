class Home{
    setCKEditor(){
        CKEDITOR.replace('_description');
    }
}

if(document.getElementById('add-order-form')){
    window.onload = () => {
        let home = new Home();
        home.setCKEditor();
    }
}