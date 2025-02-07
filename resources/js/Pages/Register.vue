<template lang="">
 
 <div class="row d-flex justify-content-center">
					<div class="col-xl-4 col-lg-5 col-md-6 col-sm-12">
						<div class="login-screen">
							<div class="login-box">
								<a href="#" class="login-logo">
									<img src="/public/img/Logo/TTC LOGO.png"  alt="Le Rouge Admin Dashboard" />
									
								</a>
								<h5>Welcome back,<br />Create Account.</h5>
								<div class="form-group">
									<input type="text" v-model="username" class="form-control" placeholder="Username" />
								</div>
								<div class="form-group">
									<input type="password"  v-model="password" class="form-control" placeholder="Password" />
								</div>
								<div class="form-group">
									<input type="password"  v-model="password_confrim" class="form-control" placeholder="Password" />
								</div>
								<div class="form-group">
									<input type="text" v-model="full_name" class="form-control" placeholder="full_name" />
								</div>
								<div class="form-group">
									<input type="text" v-model="position" class="form-control" placeholder="position" />
								</div>
								<div class="form-group">
									<input type="text" v-model="department" class="form-control" placeholder="department" />
								</div>
								<div class="form-group">
									<input type="text" v-model="phone" class="form-control" placeholder="phone" />
								</div>
								<div class="form-group">
									<input type="email" v-model="email" class="form-control" placeholder="email" />
								</div>
								<!-- <div class="form-group">
									<input type="text" v-model="role" class="form-control" placeholder="role" />
								</div>
								 -->

								
									<p class="text-danger">{{message_error}}</p>
								<div class="actions mb-4">
									
									<button type="submit" @click="Register()" class="btn btn-primary">Register</button>
								</div>
								
								<hr>
								<div class="actions align-left">
									<span class="additional-link">New here?</span>
									<a href="" class="btn btn-dark">Create an Account</a>
								</div>
							</div>
						</div>
					</div>
				</div>


</template>
<script>
import axios from 'axios';
export default {
	data() {
		return {
			url: window.location.href,
			username: '',
			password: '',
			password_confrim: '',
			full_name: '',
			position: '',
			department: '',
			phone: '',
			email: '',
			
			message_error: '',
		}
	},
	methods: {
		Register() {
			// console.log('Test 1234');
			axios.post('api/register', {
				username: this.username,
				password: this.password,
				password_confrim: this.password_confrim,
				full_name: this.full_name,
				position: this.position,
				department: this.department,
				phone: this.phone,
				email: this.email,
				
			}).then((res)=>{
				console.log(res.data);
			if(res.data.success)
			{
				this.$router.push({name: 'login'});
			}else{
				this.message_error = res.data.message;
			}
			}).catch((err)=>{
				console.log(err);
			});
			
		}
	}
    
}
</script>
<style lang="">
    
</style>