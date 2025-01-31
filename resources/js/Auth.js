//สร้าง stase,getter,action ของ Auth เพื่อเก็นค่า token และส่งค่า token ไปที่ api
class Auth {
    constructor() {
        this.token = localStorage.getItem('token') || '';
    }
    get isAuthenticated() {
        return this.token !== '';
    }
    async login(credentials) {
        try {
            const response = await axios.post('/api/login', credentials);
            this.token = response.data.token;
            localStorage.setItem('token', this.token);
            return response;
        } catch (error) {
            return error.response;
        }
    }
    async logout() {
        try {
            const response = await axios.post('/api/logout', null, {
                headers: {
                    Authorization: `Bearer ${this.token}`
                }
            });
            this.token = '';
            localStorage.removeItem('token');
            return response;
        } catch (error) {
            return error.response;
        }
    }
}


