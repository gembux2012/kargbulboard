<?xml version="1.0" encoding="UTF-8"?>

<project name="Kargvito" default="build" basedir=".">

    <!-- ============================================  -->
    <!-- Target: vars                                  -->
    <!-- ============================================  -->
    <target name="vars">
        <echo msg="Setting local variables..." />
        <echo msg="Build branch is ${buildbranch}" />
        <resolvepath propertyName="project.dir" file="${project.basedir}" />
        <echo msg="Project source dir is: ${project.dir}" />
        <php returnProperty="build.time" function="date">
            <param value="Y-m-d-H-i-s" />
        </php>
        <resolvepath propertyName="current.dir" file="/home/kargadmin/www/${buildbranch}/${build.time}" />
        <echo msg="Current build dir is: ${current.dir}" />
        <resolvepath propertyName="t4.dir" file="${project.dir}/t4" />
        <echo msg="T4 dir is: ${t4.dir}" />
    </target>




    <!-- ============================================  -->
    <!-- Target: copy                                  -->
    <!-- ============================================  -->

    <target name="copy">
        <mkdir dir="${current.dir}" />
        <echo msg="Mkdir ${current.dir}" />
        <copy todir="${current.dir}">
            <fileset dir="${project.dir}" defaultexcludes="true">
                <include name="**" />
            </fileset>
        </copy>
        <chmod file="${current.dir}/public" mode="0777" />
        <chmod file="${current.dir}/protected/Cache" mode="0777" />
    </target>

    <!-- ============================================  -->
    <!-- Target: composer                              -->
    <!-- ============================================  -->
   <!--
    <target name="composer">
        <echo msg="Install composer depedencies for T4 framework..." />
        <exec command="composer install" dir="${t4.dir}" checkreturn="true" returnProperty="exec.return" outputProperty="exec.output" />
        <if>
            <not><equals arg1="0" arg2="${exec.return}" /></not>
            <then>
                <fail message="${exec.output}" />
            </then>
        </if>
        <echo msg="${exec.output}" />
    </target>
    -->
    <!-- ============================================  -->
    <!-- Target: migrate                               -->
    <!-- ============================================  -->
    <target name="migrate">
        <echo msg="Migrations import..." />
        <exec command="
        " dir="${current.dir}/t4/framework" checkreturn="true" returnProperty="exec.return" outputProperty="exec.output" />
        <if>
            <not><equals arg1="0" arg2="${exec.return}" /></not>
            <then>
                <fail message="${exec.output}" />
            </then>
        </if>
        <echo msg="${exec.output}" />
        <echo msg="Migrations import..." />
        <exec command="php t4.php /migrate/import --module=all" dir="${current.dir}/t4/framework" checkreturn="true" returnProperty="exec.return" outputProperty="exec.output" />
        <echo msg="Migrations up..." />
        <exec command="php t4.php /migrate/up" dir="${current.dir}/t4/framework" checkreturn="true" returnProperty="exec.return" outputProperty="exec.output" />
        <if>
            <not><equals arg1="0" arg2="${exec.return}" /></not>
            <then>
                <fail message="${exec.output}" />
                <echo message="${exec.output}" />
            </then>
        </if>
        <echo msg="${exec.output}" />
        <echo message="${exec.output}" />
        <echo message="${exec.return}" />

    </target>






    <!-- ============================================  -->
    <!-- Target: symlink                                -->
    <!-- ============================================  -->
    <target name="symlink">
        <symlink target="${current.dir}" link="/home/kargadmin/www/${buildbranch}/current" overwrite="true" />
    </target>

    <target name="build" depends=" vars, copy, migrate, symlink" />

</project>