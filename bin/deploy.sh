#!/usr/bin/env bash

set -e

remote_host=
remote_path=
deploy_id=

show_help(){
    echo "Usage: $0 [OPTION]..." >&2
    echo "Deploys a software with rsync over ssh" >&2
    echo "The options -i, -p and -r are required" >&2
    echo "" >&2
    echo "  -h          Display this help screen" >&2
    echo "  -i <id>     Sets the id, a unique name for the deployment" >&2
    echo "  -p <path>   Define the base path on the server" >&2
    echo "  -r <host>   The remote server name and user" >&2
}

while getopts ":hi:p:r:" opt; do
    case ${opt} in
        h)
            show_help
            exit 1
            ;;
        r)
            remote_host="$OPTARG"
            ;;
        p)
            remote_path="$OPTARG"
            ;;
        i)
            deploy_id="$OPTARG"
            ;;
        \?)
            echo "Invalid option: -$OPTARG" >&2
            exit 1
            ;;
        :)
            echo "The option -$OPTARG requires an argument" >&2
            exit 1
            ;;
    esac
done

if [ -z "${remote_host}" ] || [ -z "${remote_path}" ] || [ -z "${deploy_id}" ]; then
    show_help
    exit 1
fi

echo "syncing ${PWD}/ to ${remote_host}:${remote_path}/${deploy_id}/"

rsync -vAax --exclude '.git*' --exclude .composer/ --exclude coverage/ --exclude node_modules/ \
    -e "ssh -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no" \
    ./ "${remote_host}:${remote_path}/${deploy_id}/"

ssh -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no "${remote_host}" "
    set -e

    if [[ -f \"${remote_path}/current/config/config.php\" ]]; then
        echo \"Configuring\"
        cp \"${remote_path}/current/config/config.php\" \"${remote_path}/${deploy_id}/config/config.php\"
    fi

    echo \"Changing symlink\"
    ln -nsf \"${remote_path}/${deploy_id}\" \"${remote_path}/current\"

    echo \"Migrating\"
    php \"${remote_path}/current/bin/migrate\"
"
