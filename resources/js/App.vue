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
  }

  }
</script>
<style lang="">
    
</style>