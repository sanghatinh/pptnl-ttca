<template lang="">
   	<!-- Loading starts -->
		<div id="loading-wrapper">
			<div class="spinner-border" role="status">
				<span class="sr-only">Loading...</span>
			</div>
		</div>
		<!-- Loading ends -->

				
		<!-- Page wrapper start -->
		<div class="page-wrapper">
		

			<!-- Page content start  -->
			<div class="page-content">
				
					
					<!-- Sidebar menu -->
					<side-barmenu v-if="store.getToken"></side-barmenu>
					<!-- Sidebar menu end -->
					 <!-- Header start -->
					<header-bar v-if="store.getToken"></header-bar>
					<!--Heder END-->
					 
				<!-- Main container stars -->
					<div class="main-container">
						
						<router-view></router-view>
						{{ store.getToken }}
					<!-- Main container end -->
	
				</div>
			<!-- Page content end -->
			</div>
		</div>
				

				

</template>
<script>

import { useStore } from './Store/Auth';
export default {
	setup() {
		const store = useStore();
		return {
			store
		}
	} ,
    data() {
    return {
      url: window.location.origin,
    }
  },

  //สร้าง Funtion Logout Authorization: 'Bearer ' + this.store.getToken,  Clear Local Storage, Clear store
//   methods: {
// 	Logout() {
// 		console.log('Logout teststst');
// 	  axios.get(`${this.url}/api/logout`,{ headers: { Authorization: 'Bearer ' + this.store.getToken } }).then((res)=>{
// 		// console.log(res.data);
// 		if (res.data.success) {
// 			//
// 			localStorage.removeItem('web_token');
// 			localStorage.removeItem('web_user');
// 			// 
// 			this.store.logout();
// 			// Go to Login Page
// 			this.$router.push({ name: 'Login' });
// 			}

// 	  }).catch((err) => {
// 		console.log(err);
// 	  });
// 	}
// }



  


  }
</script>
<style lang="">
    
</style>