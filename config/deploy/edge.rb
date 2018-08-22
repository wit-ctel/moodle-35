set :stage, :edge
set :deploy_to, "/var/www/edge.moodle.wit.ie/"
set :branch, ENV["BRANCH_NAME"] || "master"

server 'wit-mdl-web1-1718.hst.heanet.ie', user: 'moodle', roles: %w{ web app admin }
server 'wit-mdl-web2-1718.hst.heanet.ie', user: 'moodle', roles: %w{ web }
server 'wit-mdl-web3-1718.hst.heanet.ie', user: 'moodle', roles: %w{ web }
server 'wit-mdl-web4-1718.hst.heanet.ie', user: 'moodle', roles: %w{ web }