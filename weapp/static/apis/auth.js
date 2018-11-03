// import base from './base';
import regeneratorRuntime from "regenerator-runtime";
import system from '../utils/system';
var Promise = require('es6-promise');
import {
    BASE_URL
} from '../utils/constant'
export default class auth {
    static async getUserToken() {
        var app = getApp();
        // console.log('userToken', app.globalData.userToken);
        if (!app.globalData.userToken) {
            var userToken = system.syncstorage('userToken');
            if (userToken !== '') {
                app.globalData.userToken = userToken;
            } else {
                let wxLogin = system.promise(wx.login);
                try {
                    var res = await wxLogin();
                    let wxRequest = system.promise(wx.request);
                    try {
                        var r = await wxRequest({
                            url: BASE_URL + '/wechat/openid',
                            method: 'POST',
                            data: {
                                code: res.code,
                            },
                            header: {
                                'content-type': 'application/x-www-form-urlencoded'
                            }
                        });
                        if (r.errMsg === 'request:ok' && r.data.ret === 0) {
                            var data = r.data.data;
                            // console.log(data);
                            system.syncstorage('userToken', data);
                            app.globalData.userToken = data;
                        }
                    } catch (err) {
                        console.error("get session failed", err);
                    }
                } catch (e) {
                    console.error("get login failed", e);
                }
            }
        }
        return app.globalData.userToken;
    }
    static async setUserInfo(res, cb = null) {
        var app = getApp();
        if (res.errMsg === "getUserInfo:ok" && res.userInfo) {
            // console.log(res)
            app.globalData.userInfo = res.userInfo;
            var r = await this.login(res.userInfo);
            if (r === true) {
                wx.setStorageSync("userInfo", res.userInfo);
                // } else {
                //     wx.setStorageSync("userInfo", '');
            }
            if (cb !== null) {
                typeof cb == "function" &&
                    cb(that.globalData.userInfo);
                typeof cb == "string" &&
                    wx.navigateTo({ url: cb });
            }
            // console.log('cb', cb);
            return res.userInfo;
            //请求自己的服务器
        } else {
            wx.showAlert("获取用户登录态失败！" + res.errMsg);
            return false;
        }
    }

    static async login(userinfo) {
        var res = await system.request({
            url: '/wechat/userinfo',
            method: 'POST',
            data: userinfo
        });
        if (res !== undefined && res.ret === 0) {
            return true;
        }
        return res.msg;
    }
    static async getPhoneNumber(data) {
        var userToken = system.syncstorage('userToken');
        data.session = userToken.session_key;
        var res = await system.request({
            url: '/wechat/phone',
            method: 'POST',
            data: data
        });
        if (res !== undefined && res.ret === 0) {
            var data = res.data;
            var phone = data.purePhoneNumber
            if (data.countryCode != 86) {
                phone = data.phoneNumber;
            }
            system.syncstorage('phone', data);
            return phone;
        }
        return false;
    }
    static async getUerInfo() {
        var userInfo = system.syncstorage('userInfo');
        if (userInfo) {
            return userInfo;
        }
        let wxUserInfo = system.promise(wx.getUserInfo);
        try {
            res = await wxUserInfo({ lang: 'zh_CN' });
            // console.log('res', res);
        } catch (err) {
            console.log('err', err);
        }
    }
    static async saveUerInfo(data) {
        try {
            var res = await system.http({
                url: '/wechat/userinfo',
                method: 'POST',
                data: data
            });
            if (res.ret !== undefined && res.ret === 0) {
                delete data.r;
                system.syncstorage('userInfo', data);
                return true;
            }
            return false;
        } catch(e) {
            return false;
        }
    }
}
