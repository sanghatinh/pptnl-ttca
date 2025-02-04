<template>
  <div>
    <h3>User Management</h3>
    <!-- Add your user management UI here -->
    <div class="row gutters">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">

							<div class="card">
								<div class="card-body">
									<div class="table-responsive">
										<table class="table table-hover table-bordered">
											<thead>
												<tr>
													<th>#</th>
													<th>Title</th>
													<th>Comment</th>
													<th>Module</th>
													<th>Reporter</th>
													<th>Status</th>
													<th>Owner</th>
													<th>Severity</th>
													<th class="text-right">Created</th>
													<th class="text-right">Updated</th>
													<th class="text-right">Due</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td>1</td>
													<td>App crashes</td>
													<td>Kelly, can you please take a look.</td>
													<td>Main App</td>
													<td>Lewis</td>
													<td><span class="badge badge-danger">Open</span></td>
													<td>Micheal</td>
													<td class="text-danger">High</td>
													<td class="text-right">Aug-10, 2019</td>
													<td class="text-right">Sep-14, 2019</td>
													<td class="text-right">Oct-20, 2019</td>
												</tr>
												<tr>
													<td>2</td>
													<td>Saving file</td>
													<td>Let me take this up.</td>
													<td>Form Screen</td>
													<td>James</td>
													<td><span class="badge badge-success">In Progress</span></td>
													<td>Donald</td>
													<td class="text-success">Low</td>
													<td class="text-right">Aug-10, 2019</td>
													<td class="text-right">Sep-14, 2019</td>
													<td class="text-right">Oct-20, 2019</td>
												</tr>
												<tr>
													<td>3</td>
													<td>Login fail</td>
													<td>Hey, critical issue.</td>
													<td>Main App</td>
													<td>Powell</td>
													<td><span class="badge badge-danger">Open</span></td>
													<td>Glory</td>
													<td class="text-danger">High</td>
													<td class="text-right">Aug-10, 2019</td>
													<td class="text-right">Sep-14, 2019</td>
													<td class="text-right">Oct-20, 2019</td>
												</tr>
												<tr>
													<td>4</td>
													<td>Saving file</td>
													<td>Let me take this up.</td>
													<td>Form Screen</td>
													<td>James</td>
													<td><span class="badge badge-success">In Progress</span></td>
													<td>Donald</td>
													<td class="text-success">Low</td>
													<td class="text-right">Aug-10, 2019</td>
													<td class="text-right">Sep-14, 2019</td>
													<td class="text-right">Oct-20, 2019</td>
												</tr>
												<tr>
													<td>5</td>
													<td>Login fail</td>
													<td>Hey, critical issue.</td>
													<td>Main App</td>
													<td>Powell</td>
													<td><span class="badge badge-success">In Progress</span></td>
													<td>Glory</td>
													<td class="text-danger">High</td>
													<td class="text-right">Aug-10, 2019</td>
													<td class="text-right">Sep-14, 2019</td>
													<td class="text-right">Oct-20, 2019</td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</div>

						</div>
					</div>
  </div>
</template>

<script>
export default {
  name: 'User',
  data() {
    return {
      // Add your data properties here
    };
  },
  methods: {
    // Add your methods here
  }
};
</script>

<style scoped>
/* Add your styles here */
</style>
