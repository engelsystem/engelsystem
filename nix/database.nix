{ pkgs, lib }:

let
  # Database configuration
  dbConfig = lib.dbConfig;

  # Docker-based database start script
  dbStartDocker = pkgs.writeShellScriptBin "engelsystem-db-start" ''
    set -euo pipefail

    CONTAINER_NAME="engelsystem-db"
    DB_PORT="''${DB_PORT:-${toString dbConfig.port}}"
    DB_NAME="''${DB_DATABASE:-${dbConfig.database}}"
    DB_USER="''${DB_USERNAME:-${dbConfig.username}}"
    DB_PASS="''${DB_PASSWORD:-${dbConfig.password}}"
    DATA_DIR="''${DB_DATA_DIR:-}"

    # Check if container already exists
    if ${pkgs.docker-client}/bin/docker ps -a --format '{{.Names}}' | grep -q "^$CONTAINER_NAME$"; then
      # Check if it's running
      if ${pkgs.docker-client}/bin/docker ps --format '{{.Names}}' | grep -q "^$CONTAINER_NAME$"; then
        echo "Database container '$CONTAINER_NAME' is already running on port $DB_PORT"
        exit 0
      else
        echo "Starting existing database container '$CONTAINER_NAME'..."
        ${pkgs.docker-client}/bin/docker start "$CONTAINER_NAME"
        echo "Database started on port $DB_PORT"
        exit 0
      fi
    fi

    echo "Starting new MariaDB container '$CONTAINER_NAME'..."

    VOLUME_ARGS=""
    if [ -n "$DATA_DIR" ]; then
      mkdir -p "$DATA_DIR"
      VOLUME_ARGS="-v $DATA_DIR:/var/lib/mysql"
    fi

    ${pkgs.docker-client}/bin/docker run -d \
      --name "$CONTAINER_NAME" \
      -e MYSQL_ROOT_PASSWORD=root \
      -e MYSQL_DATABASE="$DB_NAME" \
      -e MYSQL_USER="$DB_USER" \
      -e MYSQL_PASSWORD="$DB_PASS" \
      -p "$DB_PORT:3306" \
      $VOLUME_ARGS \
      mariadb:10.7

    echo "Waiting for database to be ready..."
    for i in $(seq 1 30); do
      if ${pkgs.mariadb.client}/bin/mysql -h 127.0.0.1 -P "$DB_PORT" -u root -proot -e "SELECT 1" >/dev/null 2>&1; then
        echo "Database is ready!"
        echo ""
        echo "Connection details:"
        echo "  Host: 127.0.0.1"
        echo "  Port: $DB_PORT"
        echo "  Database: $DB_NAME"
        echo "  Username: $DB_USER"
        echo "  Password: $DB_PASS"
        echo ""
        echo "Root password: root"
        exit 0
      fi
      sleep 1
    done

    echo "Error: Database failed to start within 30 seconds"
    exit 1
  '';

  # Docker-based database stop script
  dbStopDocker = pkgs.writeShellScriptBin "engelsystem-db-stop" ''
    set -euo pipefail

    CONTAINER_NAME="engelsystem-db"
    REMOVE="''${1:-}"

    if ! ${pkgs.docker-client}/bin/docker ps -a --format '{{.Names}}' | grep -q "^$CONTAINER_NAME$"; then
      echo "Database container '$CONTAINER_NAME' does not exist"
      exit 0
    fi

    echo "Stopping database container '$CONTAINER_NAME'..."
    ${pkgs.docker-client}/bin/docker stop "$CONTAINER_NAME" || true

    if [ "$REMOVE" = "--remove" ] || [ "$REMOVE" = "-r" ]; then
      echo "Removing database container..."
      ${pkgs.docker-client}/bin/docker rm "$CONTAINER_NAME"
      echo "Container removed"
    else
      echo "Container stopped (use --remove to delete)"
    fi
  '';

  # Minikube-based database start script
  dbStartMinikube = pkgs.writeShellScriptBin "engelsystem-db-start-minikube" ''
    set -euo pipefail

    DB_NAME="''${DB_DATABASE:-${dbConfig.database}}"
    DB_USER="''${DB_USERNAME:-${dbConfig.username}}"
    DB_PASS="''${DB_PASSWORD:-${dbConfig.password}}"
    NAMESPACE="engelsystem"

    # Check if minikube is running
    if ! ${pkgs.minikube}/bin/minikube status | grep -q "Running"; then
      echo "Starting Minikube..."
      ${pkgs.minikube}/bin/minikube start
    fi

    # Create namespace if it doesn't exist
    ${pkgs.kubectl}/bin/kubectl create namespace "$NAMESPACE" --dry-run=client -o yaml | ${pkgs.kubectl}/bin/kubectl apply -f -

    # Apply MariaDB deployment
    cat <<EOF | ${pkgs.kubectl}/bin/kubectl apply -f -
    apiVersion: v1
    kind: Secret
    metadata:
      name: mariadb-secret
      namespace: $NAMESPACE
    type: Opaque
    stringData:
      root-password: root
      database: "$DB_NAME"
      username: "$DB_USER"
      password: "$DB_PASS"
    ---
    apiVersion: v1
    kind: PersistentVolumeClaim
    metadata:
      name: mariadb-pvc
      namespace: $NAMESPACE
    spec:
      accessModes:
        - ReadWriteOnce
      resources:
        requests:
          storage: 1Gi
    ---
    apiVersion: apps/v1
    kind: Deployment
    metadata:
      name: mariadb
      namespace: $NAMESPACE
    spec:
      replicas: 1
      selector:
        matchLabels:
          app: mariadb
      template:
        metadata:
          labels:
            app: mariadb
        spec:
          containers:
          - name: mariadb
            image: mariadb:10.7
            env:
            - name: MYSQL_ROOT_PASSWORD
              valueFrom:
                secretKeyRef:
                  name: mariadb-secret
                  key: root-password
            - name: MYSQL_DATABASE
              valueFrom:
                secretKeyRef:
                  name: mariadb-secret
                  key: database
            - name: MYSQL_USER
              valueFrom:
                secretKeyRef:
                  name: mariadb-secret
                  key: username
            - name: MYSQL_PASSWORD
              valueFrom:
                secretKeyRef:
                  name: mariadb-secret
                  key: password
            ports:
            - containerPort: 3306
            volumeMounts:
            - name: mariadb-storage
              mountPath: /var/lib/mysql
          volumes:
          - name: mariadb-storage
            persistentVolumeClaim:
              claimName: mariadb-pvc
    ---
    apiVersion: v1
    kind: Service
    metadata:
      name: mariadb
      namespace: $NAMESPACE
    spec:
      selector:
        app: mariadb
      ports:
      - port: 3306
        targetPort: 3306
      type: NodePort
    EOF

    echo "Waiting for MariaDB to be ready..."
    ${pkgs.kubectl}/bin/kubectl -n "$NAMESPACE" wait --for=condition=available --timeout=120s deployment/mariadb

    # Get NodePort
    NODE_PORT=$(${pkgs.kubectl}/bin/kubectl -n "$NAMESPACE" get svc mariadb -o jsonpath='{.spec.ports[0].nodePort}')
    MINIKUBE_IP=$(${pkgs.minikube}/bin/minikube ip)

    echo ""
    echo "MariaDB is running in Minikube!"
    echo ""
    echo "Connection details:"
    echo "  Host: $MINIKUBE_IP"
    echo "  Port: $NODE_PORT"
    echo "  Database: $DB_NAME"
    echo "  Username: $DB_USER"
    echo "  Password: $DB_PASS"
    echo ""
    echo "To connect:"
    echo "  mysql -h $MINIKUBE_IP -P $NODE_PORT -u $DB_USER -p$DB_PASS $DB_NAME"
    echo ""
    echo "Or use port-forward:"
    echo "  kubectl -n $NAMESPACE port-forward svc/mariadb 3306:3306"
  '';

  # Minikube-based database stop script
  dbStopMinikube = pkgs.writeShellScriptBin "engelsystem-db-stop-minikube" ''
    set -euo pipefail

    NAMESPACE="engelsystem"
    REMOVE="''${1:-}"

    echo "Stopping MariaDB in Minikube..."

    # Delete deployment and service
    ${pkgs.kubectl}/bin/kubectl -n "$NAMESPACE" delete deployment mariadb --ignore-not-found=true
    ${pkgs.kubectl}/bin/kubectl -n "$NAMESPACE" delete service mariadb --ignore-not-found=true

    if [ "$REMOVE" = "--remove" ] || [ "$REMOVE" = "-r" ]; then
      echo "Removing all MariaDB resources including data..."
      ${pkgs.kubectl}/bin/kubectl -n "$NAMESPACE" delete pvc mariadb-pvc --ignore-not-found=true
      ${pkgs.kubectl}/bin/kubectl -n "$NAMESPACE" delete secret mariadb-secret --ignore-not-found=true
      echo "All MariaDB resources removed"
    else
      echo "MariaDB stopped (PVC retained). Use --remove to delete data"
    fi
  '';

  # Minikube database shell (via port-forward)
  dbShellMinikube = pkgs.writeShellScriptBin "engelsystem-db-shell-minikube" ''
    set -euo pipefail

    NAMESPACE="engelsystem"
    DB_NAME="''${DB_DATABASE:-${dbConfig.database}}"
    DB_USER="''${DB_USERNAME:-${dbConfig.username}}"
    DB_PASS="''${DB_PASSWORD:-${dbConfig.password}}"
    LOCAL_PORT="''${DB_PORT:-${toString dbConfig.port}}"

    # Check if port-forward is already running
    if ! ${pkgs.netcat}/bin/nc -z 127.0.0.1 "$LOCAL_PORT" 2>/dev/null; then
      echo "Starting port-forward to MariaDB..."
      ${pkgs.kubectl}/bin/kubectl -n "$NAMESPACE" port-forward svc/mariadb "$LOCAL_PORT:3306" &
      PF_PID=$!
      trap "kill $PF_PID 2>/dev/null || true" EXIT
      sleep 2
    fi

    echo "Connecting to MariaDB..."
    exec ${pkgs.mariadb.client}/bin/mysql \
      -h 127.0.0.1 \
      -P "$LOCAL_PORT" \
      -u "$DB_USER" \
      -p"$DB_PASS" \
      "$DB_NAME" \
      "$@"
  '';

  # Database migration script
  dbMigrate = pkgs.writeShellScriptBin "engelsystem-db-migrate" ''
    set -euo pipefail

    DIR="''${ENGELSYSTEM_DIR:-$(pwd)}"

    if [ ! -f "$DIR/bin/migrate" ]; then
      echo "Error: bin/migrate not found. Are you in the engelsystem directory?"
      exit 1
    fi

    echo "Running database migrations..."
    cd "$DIR"
    exec ${lib.php}/bin/php bin/migrate "$@"
  '';

  # Database shell script
  dbShell = pkgs.writeShellScriptBin "engelsystem-db-shell" ''
    set -euo pipefail

    DB_HOST="''${DB_HOST:-${dbConfig.host}}"
    DB_PORT="''${DB_PORT:-${toString dbConfig.port}}"
    DB_NAME="''${DB_DATABASE:-${dbConfig.database}}"
    DB_USER="''${DB_USERNAME:-${dbConfig.username}}"
    DB_PASS="''${DB_PASSWORD:-${dbConfig.password}}"

    exec ${pkgs.mariadb.client}/bin/mysql \
      -h "$DB_HOST" \
      -P "$DB_PORT" \
      -u "$DB_USER" \
      -p"$DB_PASS" \
      "$DB_NAME" \
      "$@"
  '';

in
{
  # Scripts
  scripts = {
    start = dbStartDocker;
    stop = dbStopDocker;
    start-minikube = dbStartMinikube;
    stop-minikube = dbStopMinikube;
    shell-minikube = dbShellMinikube;
    migrate = dbMigrate;
    shell = dbShell;
  };

  # Apps for flake
  apps = {
    start = {
      type = "app";
      program = "${dbStartDocker}/bin/engelsystem-db-start";
    };
    stop = {
      type = "app";
      program = "${dbStopDocker}/bin/engelsystem-db-stop";
    };
    start-minikube = {
      type = "app";
      program = "${dbStartMinikube}/bin/engelsystem-db-start-minikube";
    };
    stop-minikube = {
      type = "app";
      program = "${dbStopMinikube}/bin/engelsystem-db-stop-minikube";
    };
    shell-minikube = {
      type = "app";
      program = "${dbShellMinikube}/bin/engelsystem-db-shell-minikube";
    };
    migrate = {
      type = "app";
      program = "${dbMigrate}/bin/engelsystem-db-migrate";
    };
    shell = {
      type = "app";
      program = "${dbShell}/bin/engelsystem-db-shell";
    };
  };
}
