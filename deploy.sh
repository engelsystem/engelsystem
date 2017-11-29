#!/usr/bin/env bash

set -e

remote_host=
remote_path=
deploy_id=

while getopts ":h:p:i:" opt; do
    case ${opt} in
        h)
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
    echo "Please specify -h[remote host], -p[remote path] and -i[deploy id]" >&2
    exit 1
fi

echo "syncing ${PWD}/ to ${remote_host}:${remote_path}/${deploy_id}/"

rsync -vAax --exclude '.git*' --exclude .composer/ \
    -e "ssh -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no" \
    ./ "${remote_host}:${remote_path}/${deploy_id}/"

ssh -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no "${remote_host}" "
    set -e

    if [[ -f \"${remote_path}/current/config/config.php\" ]]; then
        echo \"Config backup\"
        cp \"${remote_path}/current/config/config.php\" \"${deploy_id}-config.php\"
    fi

    echo \"Changing symlink\"
    unlink \"${remote_path}/current\"
    ln -s \"${remote_path}/${deploy_id}\" \"${remote_path}/current\"

    if [[ -f \"${deploy_id}-config.php\" ]]; then
        echo \"Restoring config\"
        cp  \"${deploy_id}-config.php\" \"${remote_path}/current/config/config.php\"
    fi
"
