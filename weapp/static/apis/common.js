// import base from './base';
import regeneratorRuntime from "regenerator-runtime";
import system from '../utils/system';
var Promise = require('es6-promise');

var QQMapWX = require('../libs/qqmap-wx-jssdk.js');
var qqmapsdk;
export default class common {

    static async getGeo() {
        let wxLocation = system.promise(wx.getLocation);
        var data = {
            city: '',
            latitude: '',
            longitude: ''
        };
        // console.log('--------begin-------------')
        var res = {};
        try {
            res = await wxLocation({ type: 'gcj02' });
            var r = await system.request({
                url: '/common/geo2address',
                method: 'GET',
                data: {
                    lat: res.latitude,
                    lng: res.longitude
                },
            });
            if (r.ret === 0) {
                let region = r.data;
                if (region === '') {
                    region = {};
                }
                delete res.errMsg;
                var data = Object.assign(res, region);
                system.syncstorage('locationInfo', data)
                getApp().globalData.location = data;
            }
        } catch(err) {
            console.log('err', err);
            var r = await system.request({
                url: '/common/ip2address',
                method: 'GET'
            });
            if (r.ret === 0) {
                data =  r.data;
                system.syncstorage('locationInfo', data)
                getApp().globalData.location = data;
            }
        }
        return data;
    }
    static async getIp() {
        var app = getApp();
        var BASE_URL = 'https://apis.map.qq.com/ws/';
        var URL_IP = BASE_URL + 'location/v1/ip';
        var res = await system.request({
            url: URL_IP,
            method: 'GET',
            data: {
                output: 'json',
                key: app.globalData.map.key
            }
        });
        var data = {};
        if (res.status === 0) {
            var result = res.result;
            data = result.ad_info;
            data.ip = result.ip;
            data.latitude = result.location.lat;
            data.longitude = result.location.lng;
        }
        return data;
    }

}
