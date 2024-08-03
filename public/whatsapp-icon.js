// set cookies
function setCookie(cname, cvalue, exdays) {
    const d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    let expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

// get cookies
function getCookie(cname) {
    if (document.cookie.length > 0) {
        c_start = document.cookie.indexOf(cname + "=");
        if (c_start != -1) {
            c_start = c_start + cname.length + 1;
            c_end = document.cookie.indexOf(";", c_start);
            if (c_end == -1) {
                c_end = document.cookie.length;
            }
            return unescape(document.cookie.substring(c_start, c_end));
        }
    }
    return null;
}

// render html whatsapp icon
function render_whatsapp_icon(data){
    if(data.allow == true){
        // Create the <a> element
        const link = document.createElement("a")
        link.href = `https://wa.me/${data.phone}`;
        link.target = "_blank";
        link.style.width    = data.styles.whatsapp_button.width || "50px";
        link.style.height   = data.styles.whatsapp_button.height || "50px";
        link.style.display = "block";
        
        // Create the <div> element
        const div = document.createElement("div");
        div.className = "whatsapp-button";
        div.title = "تواصل معنا واتساب";
        div.style.position = data.styles.whatsapp_button.position || "fixed";
        div.style.left     = data.styles.whatsapp_button.left;
        div.style.right    = data.styles.whatsapp_button.right;
        div.style.bottom   = data.styles.whatsapp_button.bottom;
        div.style.top      = data.styles.whatsapp_button.top;
        div.style.zIndex   = "1000000000000";
        div.style.cursor   = data.styles.whatsapp_button.cursor || "pointer";
        
        // Create the <img> element
        const image = document.createElement("img");
        image.className = "whatsapp-image";
        image.src = "https://line.sa/wp-content/uploads/2024/04/whatsapp.png";
        image.alt = "WhatsApp Image";
        image.style.width    = data.styles.whatsapp_image.width || "95%";
        
        // Append the <img> element to the <div> element
        link.appendChild(image);
        
        // Append the <div> element to the <a> element
        div.appendChild(link);
        
        if(data.plan_free == true){
            const link_line_sa = document.createElement("a");
            link_line_sa.style.display = "block";
            link_line_sa.href = "https://line.sa";
            link_line_sa.target = "_blank";
            link_line_sa.style.fontSize = "12px";
            link_line_sa.style.padding = "0px 8px";
            link_line_sa.style.marginTop = "25%";
            link_line_sa.style.backgroundColor = "rgba(155, 155, 155, 0.20)";
            link_line_sa.textContent = "LINE.SA";
            div.appendChild(link_line_sa);
        }
        
        // Append the <a> element to the document body or any desired parent element
        document.body.appendChild(div);
    }
}

// fetch api from our server
function FetchApiWhatsappIconData(){
    // Make a GET request
    const apiUrl = "https://app.line.sa/api/whatsapp-icon/{{store.id}}";
    fetch(apiUrl, {
        method: 'GET', // or 'POST', 'PUT', etc.
        mode: 'cors',
        headers: {
            'Content-Type': 'application/json',
            // Add any other required headers here
        },
    }).then(response => {
        if (!response.ok) {
            throw new Error('Request failed with status code ' + response.status);
        }
        return response.json(); // or response.text() for non-JSON responses
    }).then(async data => {
        // Process the response data
        await setCookie('whatsapp_icon_lineSa',JSON.stringify(data),2);
        await render_whatsapp_icon(data);
    }).catch(error => {
        console.error('Error:', error);
    });
}

// render whatapp icon
(async function(){
    const data_icon = await getCookie('whatsapp_icon_lineSa');
    if(data_icon == null){
        await FetchApiWhatsappIconData();
    } else {
        await render_whatsapp_icon(JSON.parse(data_icon));
    }
})();