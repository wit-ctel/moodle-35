
set :stage, :production
set :deploy_to, "/var/www/moodle.wit.ie/"
set :branch, ENV["BRANCH_NAME"] || "master"

# Simple Role Syntax
# ==================
# Supports bulk-adding hosts to roles, the primary
# server in each group is considered to be the first
# unless any hosts have the primary property set.
# Don't declare `role :all`, it's a meta role
#role :web, %w{wit@wit-moodle-web5.heanet.ie}


# Extended Server Syntax
# ======================
# This can be used to drop a more detailed server
# definition into the server list. The second argument
# something that quacks like a hash can be used to set
# extended properties on the server.
server 'wit-mdl-web1-1718.hst.heanet.ie', user: 'moodle', roles: %w{ web app admin }
server 'wit-mdl-web2-1718.hst.heanet.ie', user: 'moodle', roles: %w{ web app }
server 'wit-mdl-web3-1718.hst.heanet.ie', user: 'moodle', roles: %w{ web app }
server 'wit-mdl-web4-1718.hst.heanet.ie', user: 'moodle', roles: %w{ web app }
server 'wit-mdl-web5-1718.hst.heanet.ie', user: 'moodle', roles: %w{ web app }

# you can set custom ssh options
# it's possible to pass any option but you need to keep in mind that net/ssh understand limited list of options
# you can see them in [net/ssh documentation](http://net-ssh.github.io/net-ssh/classes/Net/SSH.html#method-c-start)
# set it globally
#  set :ssh_options, {
#    keys: %w(/home/rlisowski/.ssh/id_rsa),
#    forward_agent: false,
#    auth_methods: %w(password)
#  }
# and/or per server
# server 'example.com',
#   user: 'user_name',
#   roles: %w{web app},
#   ssh_options: {
#     user: 'user_name', # overrides user setting above
#     keys: %w(/home/user_name/.ssh/id_rsa),
#     forward_agent: false,
#     auth_methods: %w(publickey password)
#     # password: 'please use keys'
#   }
# setting per server overrides global ssh_options
