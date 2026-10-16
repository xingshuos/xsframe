/* 设置ajax请求 request.getHeader("x-requested-with"); 为 null，则为传统同步请求，为 XMLHttpRequest，则为 Ajax 异步请求。 */
define(['axios', 'sweetalert'], (axios, swal) => {
    axios.defaults.headers.common['x-requested-with'] = 'XMLHttpRequest';
    axios.defaults.withCredentials = true;
    axios.interceptors.response.use(function (response) {
        return response.data.data;
    }, function (error) {
        console.log('err', error.response);

        let retData = error.response;

        if (retData) {

            switch (retData.data.code) {
                case 404:
                    swal({
                        type: 'error',
                        title: retData.data.msg,
                        showConfirmButton: false,
                        toast: true,
                        timer: 1000
                    });
                    break;
                case 422:
                    let url = retData.config.url;
                    if (url.indexOf('/v1/common/sms/') === -1) {
                        let errorinfo = retData.data.msg;
                        let keys = Object.keys(errorinfo);
                        let messages = keys.map(function (key) {
                            return errorinfo[key].join('\n');
                        });
                        swal({
                            type: 'error',
                            title: messages.join('\n'),
                            showConfirmButton: false,
                            toast: true,
                            timer: 1000
                        });
                    } else {
                        return Promise.reject(error)
                    }
                    break;
                case 403:
                    swal({
                        type: 'error',
                        title: '无权限' + retData.data.msg,
                        showConfirmButton: false,
                        toast: true,
                        timer: 1000,
                    });
                    break;
                case 401:
                    swal({
                        type: 'error',
                        title: "请先登录",
                        showConfirmButton: false,
                        toast: true,
                        timer: 1000
                    });
                    if( retData.data.msg ){
                        setTimeout(() => {
                            window.location.href = retData.data.msg;
                        },1000);
                    }
                    break;
                case 500:
                    // console.log('server error');
                    swal({
                        type: 'error',
                        title: retData.data.msg ? retData.data.msg : '服务器错误',
                        showConfirmButton: false,
                        toast: true,
                        timer: 1000
                    });
                    break;
                case 429:
                    // console.log('server error');
                    swal({
                        type: 'error',
                        title: '操作太频繁',
                        showConfirmButton: false,
                        toast: true,
                        timer: 1000
                    });
                    break;
                default:
            }
            return Promise.reject(retData.data)
        } else {
            return Promise.reject(error)
        }
    });
    return axios;
});