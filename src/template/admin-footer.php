            </main>
            
            <!-- Footer -->
            <footer class="bg-white p-4 border-t">
                <div class="container mx-auto">
                    <div class="flex flex-col md:flex-row justify-between items-center">
                        <div class="mb-4 md:mb-0">
                            <p class="text-sm text-gray-600">&copy; <?= date('Y') ?> <?= config('app.name') ?>. All rights reserved.</p>
                        </div>
                        <div class="text-sm text-gray-500">
                            <p>Admin Panel v1.0</p>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    
    <!-- Admin Panel Scripts -->
    <script>
        // Mobile sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const closeSidebarButton = document.getElementById('close-sidebar-button');
            const mobileSidebar = document.getElementById('mobile-sidebar');
            
            if (mobileMenuButton && mobileSidebar) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileSidebar.classList.toggle('hidden');
                });
            }
            
            if (closeSidebarButton && mobileSidebar) {
                closeSidebarButton.addEventListener('click', function() {
                    mobileSidebar.classList.add('hidden');
                });
            }
            
            // User dropdown menu
            const userMenuButton = document.getElementById('user-menu-button');
            const userMenu = document.getElementById('user-menu');
            
            if (userMenuButton && userMenu) {
                userMenuButton.addEventListener('click', function() {
                    userMenu.classList.toggle('hidden');
                });
                
                // Close the dropdown when clicking outside
                document.addEventListener('click', function(event) {
                    if (!userMenuButton.contains(event.target) && !userMenu.contains(event.target)) {
                        userMenu.classList.add('hidden');
                    }
                });
            }
        });
    </script>
</body>
</html>
