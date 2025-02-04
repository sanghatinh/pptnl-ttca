
<template lang="">
   
<!-- Sidebar wrapper start -->
   <nav id="sidebar" class="sidebar-wrapper">
				
				<!-- Sidebar brand start  -->
				<div class="sidebar-brand">
					<router-link to="/" class="logo">
						<img :src="`${url}/img/Logo/TTC LOGO FFF.png`" alt="Le Rouge Admin Dashboard" />
						<span class="text-logo-company">TTC ATTAPEU SUGAR CANE SOLE CO.,LTD</span>
						

					</router-link>
					
				</div>
				<!-- Sidebar brand end  -->

				<!-- Sidebar content start -->
				<div class="sidebar-content">

					<!-- sidebar menu start -->
					<div class="sidebar-menu">
						<ul>
							<li class="header-menu">General</li>
							<li class="sidebar-dropdown active">
								<a href="#">
									<i class="icon-devices_other"></i>
									<span class="menu-text">Dashboards</span>
								</a>
								<div class="sidebar-submenu">
									<ul>
										<li>
											<router-link to="/">
												<span class="menu-text">Admin Dashboard</span>
											</router-link>
										</li>
										<li>
											<a href="sales-dashboard.html">Sales Dashboard</a>
										</li>
										<li>
											<a href="crm-dashboard.html">CRM Dashboard</a>
										</li>
										
									</ul>
								</div>
							</li>
						</ul>
						<!-- Quan Ly He thong -->
						<ul>
							<!-- <li class="header-menu">Quản lý hệ thống</li> -->
							<li class="sidebar-dropdown active">
								<a href="#" >
									<i class="icon-devices_other"></i>
									<span class="menu-text">Quản lý hệ thống</span>
								</a>
								<div class="sidebar-submenu">
									<ul>
										<li>
											<router-link to="/user" :class="$route.path=='/user'?'current-page':''"> 
												<span>Danh sách User</span>
											</router-link>
											
										</li>
										<li>
											<router-link to="/permission" :class="$route.path=='/permission'?'current-page':''">
												<span>Cấp quyền</span>
											</router-link>
										</li>
										<li>
											<router-link to="/role" :class="$route.path=='/role'?'current-page':''">
												<span>Nhóm nhân viên</span>
											</router-link>
										</li>
										
									</ul>
								</div>
							</li>
						</ul>
					</div>
					<!-- sidebar menu end -->
					 

				</div>
				<!-- Sidebar content end -->
				
			</nav>
			<!-- Sidebar wrapper end -->



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
<style scoped>
	.text-logo-company{
	font-size: 12px;
	margin-top: 10px;
	margin-left: 10px;
	color: #fff;
	font-weight: bold;
		/* กลื่งกางแนวตั้ง */
		transform: rotate(90deg);
		/* กลื่งกางแนวนอน */
		transform: rotate(0deg);

	}


    
</style>