import authApi from '../apis/auth';
import Tips from './Tips';
var Promise = require('es6-promise');
import regeneratorRuntime from "regenerator-runtime";
import {
    BASE_URL
} from '../utils/constant';
export default {
    attachInfo() {
        let res = wx.getSystemInfoSync();

        wx.WIN_WIDTH = res.screenWidth;
        wx.WIN_HEIGHT = res.screenHeight;
        wx.IS_IOS = /ios/i.test(res.system);
        wx.IS_ANDROID = /android/i.test(res.system);
        wx.STATUS_BAR_HEIGHT = res.statusBarHeight;
        wx.DEFAULT_HEADER_HEIGHT = 46; // res.screenHeight - res.windowHeight - res.statusBarHeight
        wx.DEFAULT_CONTENT_HEIGHT = res.screenHeight - res.statusBarHeight - wx.DEFAULT_HEADER_HEIGHT;
        wx.IS_APP = true;
        wx.CONTENT_HEIGHT = res.windowHeight;
        wx.logger = wx.getLogManager();
        wx.showAlert = (text, title = '提示') => {
            wx.showModal({
                title: title,
                content: text,
                showCancel: false
            });
        }

        wx.showConfirm = (options) => {
            wx.showModal(options);
        }
        wx.showTips = (title, duration = 1000) => {
            wx.showToast({
                title: title,
                icon: 'none',
                mask: false,
                duration: duration
            });
        }
        wx.showSuccess = (title, duration = 1000) => {
            wx.showToast({
                title: title,
                icon: 'success',
                mask: true,
                duration: duration
            });
        }
    },
    storage(key, value) {
        if (arguments.length == 1) {
            return this.getStorage(key, false);
        } else {
            return this.setStorage(key, value, false);
        }
    },
    //同步读取、设置localstorage
    syncstorage(key, value) {
        if (arguments.length == 1) {
            return this.getStorage(key, true);
        } else {
            return this.setStorage(key, value, true);
        }
    },
    //封装wx.getStorage方法 默认同步获取 flag: true->同步 false->异步
    getStorage(key, flag = true) {
        //异步
        if (!flag) {
            let myRequest = this.promise(wx.getStorage);
            return myRequest({
                key: key
            });
            //同步
        } else {
            return wx.getStorageSync(key);
        }
        return '';
    },
    //封装wx.setStorage 默认同步设置 flag: true->同步 false->异步
    setStorage(key, value, flag = true) {
        //异步
        if (!flag) {
            let myRequest = this.promise(wx.setStorage);
            return myRequest({
                key: key,
                data: value
            });
            //同步
        } else {
            wx.setStorageSync(key, value);
            return wx.getStorageSync(key);
        }
        return '';
    },

    //返回Promise对象
    promise(fn) {
        return function(obj = {}) {
            return new Promise((resolve, reject) => {
                obj.success = function(res) {
                    // console.log("res--" + res);
                    resolve(res);
                }
                obj.fail = function(res) {
                    reject(res);
                }
                fn(obj);
            })
        }
    },
    request(options) {
        return new Promise((resolve, reject) => {
            var app = getApp();
            if (options.data === undefined) {
                options.data = {};
            }
            var header = {};
            wx.showNavigationBarLoading();
            Tips.loading();
            var method = options.method || 'POST';
            if (method === 'POST' || method === 'DELETE') {
                header['content-type'] = 'application/x-www-form-urlencoded';
            } else {
                header['content-type'] = 'application/json';
            }
            var url = '';
            if (options.url.substr(0, 4) === 'http') {
                url = options.url;
            } else {
                url = BASE_URL + options.url;
                var userToken = app.getUserToken();
                // var userinfo = app.globalData.auth;
                // console.log('request userToken', userToken);
                if (userToken) {
                    header['User-Token'] = userToken.openid;
                    // options.data['user-token'] = userToken.openid;
                }
                options.data['r'] = Math.random();
            }
            wx.request({
                url: url,
                method: method,
                header: header,
                data: options.data,
                dataType: 'json',
                success: res => {
                    Tips.loaded();
                    resolve(res.data)
                },
                fail: err => {
                    Tips.loaded();
                    reject(err)
                },
                complete: res => {
                    wx.hideNavigationBarLoading();
                }
            })
        })
    },
    async http(options) {
        let wxRequest = this.promise(wx.request);
        try {
            var app = getApp();
            if (options.data === undefined) {
                options.data = {};
            }
            var header = {};
            wx.showNavigationBarLoading();
            Tips.loading();
            var method = options.method || 'POST';
            if (method === 'GET') {
                header['content-type'] = 'application/json';
            } else {
                header['content-type'] = 'application/x-www-form-urlencoded';
            }
            var url = '';
            if (!options.url || options.url.substr(0, 4) === 'http') {
                url = options.url;
            } else {
                url = BASE_URL + options.url;
                var userToken = await app.getUserToken();
                // var userinfo = app.globalData.auth;
                // console.log('request userToken', userToken);
                if (userToken) {
                    header['User-Token'] = userToken.openid;
                    // options.data['user-token'] = userToken.openid;
                }
                options.data['r'] = Math.random();
            }
            var params = {
                url: url,
                method: method,
                header: header,
                data: options.data,
                dataType: 'json'
            };
            // console.log('begin http result:', params);
            var res = await wxRequest(params);

            // console.log('http result:', res);
            // Tips.loaded();
            // wx.hideNavigationBarLoading();
            if (res.statusCode === 200 && res.errMsg === 'request:ok') {
                var x = wx.logger.log('http request', params, res.data);
                wx.reportMonitor('CARD_INDEX', 1);
                // console.log(wx.logger);
                // var json = JSON.parse(res.data);
                return res.data;
            }
            wx.logger.error('http request error', params, res);
            return false;
        } catch (err) {
            console.error("http request failed", err);
        } finally {
            Tips.loaded();
            wx.hideNavigationBarLoading();
        }
        return null;
    },
    async upload(options) {
        let wxUploadFile = this.promise(wx.uploadFile);
        try {
            var app = getApp();
            if (options.data === undefined) {
                options.data = {};
            }
            var header = {};
            wx.showNavigationBarLoading();
            Tips.loading();
            var method = 'POST';
            header["Content-Type"] = "multipart/form-data";
            var url = '';
            if (options.url.substr(0, 4) === 'http') {
                url = options.url;
            } else {
                url = BASE_URL + options.url;
                var userToken = await app.getUserToken();
                // console.log('userToken: ', userToken);
                if (userToken) {
                    header['User-Token'] = userToken.openid;
                }
                options.data['r'] = Math.random();
            }
            var res = await wxUploadFile({
                url: url,
                method: method,
                name: "file",
                header: header,
                filePath: options.filePath,
                formData: options.data,
                dataType: 'json'
            });
            // console.log(res);
            // Tips.loaded();
            // wx.hideNavigationBarLoading();
            if (res.statusCode !== 200) {
                return false;
            }
            var json = JSON.parse(res.data);
            return json;
        } catch (err) {
            console.error("upload file failed", err);
        } finally {
            Tips.loaded();
            wx.hideNavigationBarLoading();
        }
        return null;
    }
}
