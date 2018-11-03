// import base from './base';
import regeneratorRuntime from "regenerator-runtime";
import system from '../utils/system';
var Promise = require('es6-promise');

export default class card {
    static async list() {
        return await this.get();
    }
    static async info(id = 0) {
        return await this.get({ id: id });
    }
    static async get(data = {}) {
        var res = await system.http({
            url: '/card/card',
            method: 'GET'
        });
        // console.log(res)
        if (res !== undefined && res.ret === 0) {
            return res.data;
        }
        return false;
    }
    static async create(data) {
        return await this.save('POST', data);
    }
    static async modify(data) {
        return await this.save('PUT', data);
    }
    static async delete(id) {
        return await this.save('DELETE', { id: id });
    }
    static async save(postType, data = {}) {

        var res = await system.http({
            url: '/card/card',
            method: postType,
            data: data
        });
        // console.log(res)
        if (res !== undefined && res.ret === 0) {
            // system.syncstorage('userId', res.data.user_id);
            return true;
        }
        return res.msg;
    }
    static async info(id) {
        var res = await system.http({
            url: '/card/card',
            method: 'GET',
            data: { id: id }
        });
        // console.log(res)
        if (res !== undefined && res.ret === 0) {
            return res.data;
        }
        return false;
    }
    static async view(id, uid = 0) {
        return await this.control('viewed', id, uid);
    }
    static async collect(id, uid = 0) {
        return await this.control('collected', id, uid);
    }

    static async like(id, uid = 0) {
        return await this.control('liked', id, uid);
    }
    static async control(type, id, uid = 0) {
        var res = await system.http({
            url: '/card/control',
            method: 'POST',
            data: {
                id: id,
                user_id: uid,
                type: type
            }
        });
        // console.log(res)
        if (res !== undefined && res.ret === 0) {
            return true;
        }
        return res;
    }

    static async box() {
        var res = await system.http({
            url: '/card/box',
            method: 'GET'
        });
        // console.log(res)
        if (res !== undefined && res.ret === 0) {
            return res.data;
        }
        return false;
    }
    static async mine() {
        var res = await system.http({
            url: '/card/mine',
            method: 'GET'
        });
        // console.log(res)
        if (res !== undefined && res.ret === 0) {
            return res.data;
        }
        return false;
    }
    static async remark(id, remark) {
        var res = await system.http({
            url: '/card/part',
            method: 'PUT',
            data: {
                id: id,
                remark: remark
            }
        });
        // console.log(res)
        if (res !== undefined && res.ret === 0) {
            return true;
        }
        return false;
    }
    static async part(id, type) {
        var res = await system.http({
            url: '/card/part',
            method: 'GET',
            data: {
                id: id,
                type: type
            }
        });
        // console.log(res)
        if (res !== undefined && res.ret === 0) {
            return res.data;
        }
        return false;
    }
    static async all(page = 1, limit = 8, data = {}) {
        data.page = page;
        data.limit = limit;
        var res = await system.http({
            url: '/card/all',
            method: 'GET',
            data: data
        });
        // console.log(res)
        if (res !== undefined && res.ret === 0) {
            return res.data;
        }
        return false;
    }

    static async edit(data = null) {
        var postType = 'POST';
        // console.log(data);
        if (data === null) {
            var res = await system.http({
                url: '/card/edit',
                method: 'GET'
            });
            if (!res == false && res.ret === 0) {
                return res.data;
            }
            return false;
        }
        var res;
        // console.log(data.avatar.substr(0, 10));
        if (data.avatar != '' && data.avatar.substr(0, 10) == 'http://tmp') {
            data.imgIndex = 1;
            // data.r = Math.random();
            // console.log(data);
            res = await system.upload({
                url: "/card/edit",
                filePath: data.avatar,
                name: "avatar",
                data: data
            });
            // console.log(data);
        } else {
            res = await system.http({
                url: '/card/edit',
                method: 'POST',
                data: data
            });
        }

        if (!res == false && res.ret === 0) {
            return true;
        }
        return res;
    }
    static async exchange(id, type = 4, formId) {
        var res = await system.http({
            url: '/card/exchange',
            method: 'POST',
            data: {
                id,
                formId,
                type
            }
        });
        if (res !== undefined && res.ret === 0) {
            return true;
        }
        return res;
    }
    static async apply(status, page, limit) {
        var res = await system.http({
            url: '/card/exchange',
            method: 'GET',
            data: {
                page,
                limit,
                status
            }
        });
        // console.log(res)
        if (res !== undefined && res.ret === 0) {
            return res.data;
        }
        return false;
    }
    static async reply(id, status, formId) {
        var res = await system.http({
            url: '/card/exchange',
            method: 'PUT',
            data: {
                id,
                status,
                formId
            }
        });
        // console.log(res)
        if (res !== undefined && res.ret === 0) {
            return true;
        }
        return res.msg ? res.msg : false;
    }
}
