<script>
// render html whatsapp icon
function render_whatsapp_icon(){
     console.log("{{app}}");
    // if(data.allow == true){
    if("{{app.wahtsapp_icon_status}}" == true){
        // Create the <a> element
        const link = document.createElement("a")
        link.href = "https://wa.me/{{app.custom_merchant_phone}}";
        link.target = "_blank";
        link.style.width    = "{{app.whatsapp_icon_width}}" || "50px";
        link.style.height   = "{{app.whatsapp_icon_height}}" || "50px";
        link.style.display = "block";
        
        // Create the <div> element
        const div = document.createElement("div");
        div.className = "whatsapp-button";
        div.title = "تواصل معنا واتساب";
        div.style.position = "fixed";
        div.style.left     = "{{app.whatsapp_icon_left}}";
        div.style.right    = "{{app.whatsapp_icon_right}}";
        div.style.bottom   = "{{app.whatsapp_icon_bottom}}";
        div.style.top      = "{{app.whatsapp_icon_top}}";
        div.style.zIndex   = "1000000000000";
        div.style.cursor   = "pointer";
        
        // Create the <img> element
        const image = document.createElement("img");
        image.className = "whatsapp-image";
        image.src = "https://line.sa/wp-content/uploads/2024/04/whatsapp.png";
        image.alt = "WhatsApp Image";
        image.style.width    = "{{app.whatsapp_icon_width}}" || "95%";
        
        // Append the <img> element to the <div> element
        link.appendChild(image);
        
        // Append the <div> element to the <a> element
        div.appendChild(link);
        
        //if(data.plan_free == true){
            //const link_line_sa = document.createElement("a");
            //link_line_sa.style.display = "block";
            //link_line_sa.href = "https://line.sa";
            //link_line_sa.target = "_blank";
            //link_line_sa.style.fontSize = "12px";
            //link_line_sa.style.padding = "0px 8px";
            //link_line_sa.style.marginTop = "25%";
            //link_line_sa.style.backgroundColor = "rgba(155, 155, 155, 0.20)";
            //link_line_sa.textContent = "LINE.SA";
            //div.appendChild(link_line_sa);
       // }
        
        // Append the <a> element to the document body or any desired parent element
        document.body.appendChild(div);
    }
}
render_whatsapp_icon();
</script>