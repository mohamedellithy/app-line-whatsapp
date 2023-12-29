var clinet_phone_no = await Common.db_query(`SELECT token FROM sp_accounts WHERE token = '` + instance_id + `'`);
var data_mo = { caption: "" };
if (limit.mass == 'expiration_date') {
    data_mo = {
        caption: `
    		            عزيزي العميل\n
    		            تم انتهاء باقتك في رسائل واتساب لاين.\n
    		            يمكنك تجديد الباقة مباشرة من صفحة واتساب لاين\n
    		            https://line.sa/19505
		            `
    };
} else if (limit.mass == 'count_messages') {
    data_mo = {
        caption: `
    		            عزيزي العميل\n
    		            تم استنفاذ باقتك فى رسائل واتساب لاين.\n
    		            يمكنك تجديد الباقة مباشرة من صفحة واتساب لاين\n
    		            https://line.sa/19505
    	            `
    };
}

sessions["652679F5BEB97"].sendMessage("201026051966", data_mo).then(async(message) => {});