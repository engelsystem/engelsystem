
-- not only allows access to backup databases like `helferinnensystem_2023`,
-- but also makes it so that we can DROP and re-CREATE the `helferinnensystem`
-- table without loosing the privileges.
GRANT ALL PRIVILEGES ON `helferinnensystem%` . * TO helferinnensystem ;
