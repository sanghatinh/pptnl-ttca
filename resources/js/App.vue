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

				<!-- Header start -->
				 <header-bar></header-bar>
				 <!--Heder END-->
				
		

				<!-- Sidebar menu -->
				 <side-barmenu></side-barmenu>
				<!-- Sidebar menu end -->
				
				<!-- Main container start -->
				<div class="main-container">
					<router-view></router-view>

			

				</div>
				<!-- Main container end -->

			</div>
			<!-- Page content end -->

		</div>
</template>
<script>
export default {
    data() {
    return {
      url: window.location.origin,
    }
  }
 
}
</script>
<style lang="">
    
</style>