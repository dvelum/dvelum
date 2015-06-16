<?php
interface Backend_Designer_Generator_Component
{
    /**
     * Generate and add components to the designer project
     * @param Designer_Project $project
     * @param string $id - local name
     * @param string $parentId - parent object id
     * @return boolean
     */
	public function addComponent(Designer_Project $project , $id , $parentId = false);
}