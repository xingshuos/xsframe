/* 设置ajax请求 request.getHeader("x-requested-with"); 为 null，则为传统同步请求，为 XMLHttpRequest，则为 Ajax 异步请求。 */
define(['axios','sweetalert'],(axios,swal) => {
    axios.defaults.headers.common['x-requested-with'] = 'XMLHttpRequest';
    axios.defaults.withCredentials = true;
    axios.interceptors.response.use(function(response) {
        return response.data.data;
    }, function(error) {
        if (error.response) {
            switch (error.response.status) {
                case 400:
                    swal({
                        type: 'error',
                        title: error.response.data.error,
                        showConfirmButton: false,
                        toast: true,
                        timer: 1000
                    });
                    break;
                case 422:
                    var url = error.response.config.url;
                    if (url.indexOf('/v1/common/sms/') === -1) {
                        var errorinfo = error.response.data.errors;
                        var keys = Object.keys(errorinfo);
                        var messages = keys.map(function(key) {
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
                        title: '无权限' + error.response.data.error,
                        showConfirmButton: false,
                        toast: true,
                        timer: 1000
                    });
                    break;
                case 401:
                    window.location.href = '/login';
                    break;
                case 500:
                    console.log('server error');
                    // swal({
                    //     type: 'error',
                    //     title: '服务器错误',
                    //     showConfirmButton: false,
                    //     toast: true,
                    //     timer: 1000
                    // });
                    break;
                case 429:
                    console.log('server error');
                    // swal({
                    //     type: 'error',
                    //     title: '操作太频繁',
                    //     showConfirmButton: false,
                    //     toast: true,
                    //     timer: 1000
                    // });
                    break;
                default:
                    console.log(error.response)
            }
            return Promise.reject(error.response.data)
        } else {
            return Promise.reject(error)
        }
    });
    return axios;
});